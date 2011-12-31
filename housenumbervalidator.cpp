/*
	v111228
	
	Copyright (C) 2011 Markus Brenneis
	
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

#include <iostream>
#include <QDebug>
#include <QDomDocument>
#include <QFile>
#include <QList>
#include <QString>
#include <QStringList>
#include <QTime>

using namespace std;

struct binTree;

typedef binTree* pBinTree;

struct housenumber;

struct housenumber {
	double lat, lon;
	int id;
	bool ignore, isWay, isHnr;
	QString name, shop, number, street, postcode, city, country, nodeId;
	pBinTree dupe;
};

struct binTree {
	pBinTree left;
	pBinTree right;
	double lat, lon;
	int id;
	bool ignore, isWay;
	QString address;
	pBinTree dupe;
};

QString qsAssumeCountry="", qsAssumeCity="", qsAssumePostcode="", filename="input.osm";
bool bIgnoreFixme=true, bIgnoreNote=true, bIgnoreCityHint=false, bCheckPostcodeNumber=false, bCheckStreetSuffix=false;
int lines=9200594, lineCount=0, dupeCount=0, hnrCount=0, incompleteCount=0, brokenCount=0, iCheckPostcodeChars=-1;

QTextStream duplicatesStream, incompleteStream, brokenStream;

pBinTree treeHousenumbers, treeNodes;

bool isComplete(housenumber &hnr);
QString qsGenerateDupeOutput(pBinTree hnr);
QString qsGenerateIncompleteOutput(housenumber hnr, int i);
QString qsGenerateBrokenStreetOutput(housenumber hnr);
QString qsGenerateBrokenPostcodeOutput(housenumber hnr);
QString qsGenerateLink(housenumber hnr);
QString qsGenerateLink(pBinTree hnr);
void vGetLatLonForWay(double &lat, double &lon, QString ref, pBinTree tree);
void insert(pBinTree &element, pBinTree &root);
void insertNode(housenumber hnr);
void housenumberToBinTree(housenumber hnr, pBinTree &node);
void nodeToBinTree(housenumber hnr, pBinTree &node);
void inorder(pBinTree &root);

int main(int argc, const char* argv[]){ 
	QTime now;
	now.start();
        
	if(argc>1) {
		if(QString(argv[1])=="--help" || QString(argv[1])=="-h") {
			qDebug() << "Usage: ./housenumbervalidator [FILENAME.hnr.osm [OPTIONS]]\n";
			qDebug() << "Options:";
			qDebug() << "  -ac=XX  --assume-country=XX       When a housenumber does not have a addr:country value, it is set to XX";
			qDebug() << "  -ai=XX  --assume-city=XX";
			qDebug() << "  -ap=XX  --assume-postcode=XX";
			qDebug() << "  -cpn    --check-postcode-number   When addr:postcode is not a number, save entry in broken.txt";
			qDebug() << "  -cpc=X  --check-postcode-chars=X  When addr:postcode does not have X characters, save entry in broken.txt";
			qDebug() << "  -css    --check-street-suffix     When addr:street ends with 'str' or 'str.', save entry in broken.txt";
			qDebug() << "  -iih    --ignore-city-hint        Objects which hava a addr:city tag (and no other addr:* tag) are not considered to be a house number, and thus are not listed as incomplete";
			qDebug() << "  -nif,   --not-ignore-fixme        do output ways/nodes which have a fixme tag";
			qDebug() << "  -nin,   --not-ignore-note         do output ways/nodes which have a note tag";
			qDebug() << "  -h      --help                    Print this help";
			qDebug() << "\ncompiled on" << __DATE__;
			return 0;
		}
		
		filename=QString(argv[1]);
		
		for(int i=2; i<argc; i++){
			if(QString(argv[i])=="-nif" || QString(argv[i])=="--not-ignore-fixme") bIgnoreFixme=false;
			else if(QString(argv[i])=="-nin" || QString(argv[i])=="--not-ignore-note") bIgnoreNote=false;
			/*else if(QString(argv[i])=="-in" or QString(argv[i])=="--ignore-note") ignorenote=TRUE;
			else if(QString(argv[i])=="-npc" or QString(argv[i])=="--no-postcode-count") nopostcodecount=TRUE;
			else if(QString(argv[i])=="-ich" or QString(argv[i])=="--ignore-country-hint") ignorecountryhint=TRUE;*/
			else if(QString(argv[i])=="-iih" or QString(argv[i])=="--ignore-city-hint") bIgnoreCityHint=true;
			/*else if(QString(argv[i])=="-iph" or QString(argv[i])=="--ignore-postcode-hint") ignorepostcodehint=TRUE;
			else if(QString(argv[i])=="-ish" or QString(argv[i])=="--ignore-street-hint") ignorestreethint=TRUE;
			else if(QString(argv[i])=="-inh" or QString(argv[i])=="--ignore-number-hint") ignorenumberhint=TRUE;*/
			else if(QString(argv[i]).contains("-ac=") || QString(argv[i]).contains("--assume-country=")) qsAssumeCountry=QString(argv[i]).split("=")[1];
			else if(QString(argv[i]).contains("-ai=") || QString(argv[i]).contains("--assume-city=")) qsAssumeCity=QString(argv[i]).split("=")[1];
			else if(QString(argv[i]).contains("-ap=") || QString(argv[i]).contains("--assume-postcode=")) qsAssumePostcode=QString(argv[i]).split("=")[1];
// 			else if(QString(argv[i]).contains("-wo=")) dupesperosm=QString(argv[i]).mid(4).toInt();
// 			else if(QString(argv[i]).contains("--write-osm=")) dupesperosm=QString(argv[i]).mid(12).toInt();
			else if(QString(argv[i]).contains("--check-postcode-number") || QString(argv[i]).contains("-cpn")) bCheckPostcodeNumber=true;
			else if(QString(argv[i]).contains("--check-postcode-chars=") || QString(argv[i]).contains("-cpc=")) iCheckPostcodeChars=QString(argv[i]).split("=")[1].toInt();
			else if(QString(argv[i]).contains("--check-street-suffix") || QString(argv[i]).contains("-css")) bCheckStreetSuffix=true;
			else {
				qDebug() <<  "unknown option " << argv[i];
				return(1);
			}
		}
	}
	
	treeHousenumbers=NULL;
	treeNodes=NULL;
	
	//open input file
	QFile file(filename);
	if (!file.open(QIODevice::ReadOnly | QIODevice::Text)) {
		qDebug() << "couldn't open" << filename;
		return(2);
	}
	QTextStream in(&file);
	in.setCodec("UTF-8");
	
	QFile duplicatesFile("dupes.txt");
	duplicatesFile.remove();
	if (!duplicatesFile.open(QIODevice::WriteOnly | QIODevice::Text)) {
		qDebug() << "couldn't open dupes.txt";
		file.close();
		return(3);
	}
	duplicatesStream.setDevice(&duplicatesFile);
	duplicatesStream.setCodec("UTF-8");
	duplicatesStream << "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
	
	QFile incompleteFile("incomplete.txt");
	incompleteFile.remove();
	if (!incompleteFile.open(QIODevice::WriteOnly | QIODevice::Text)) {
		qDebug() << "couldn't open incomplete.txt";
		file.close();
		return(3);
	}
	incompleteStream.setDevice(&incompleteFile);
	incompleteStream.setCodec("UTF-8");
	incompleteStream << "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
	
	QFile brokenFile("broken.txt");
	brokenFile.remove();
	if (!brokenFile.open(QIODevice::WriteOnly | QIODevice::Text)) {
		qDebug() << "couldn't open broken.txt";
		file.close();
		return(3);
	}
	brokenStream.setDevice(&brokenFile);
	brokenStream.setCodec("UTF-8");
	brokenStream << "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
	
	housenumber hnr;
	
	if(lines>0) qDebug() << "NOTE: You have to set the 'lines' variable by hand in order to get sensible progress information";
	if(!filename.endsWith(".hnr.osm")) qDebug() << "NOTE: If the osm-file is big, you should filter it by executing './filter input.osm'.";
	
	// loop through all lines
	while(!in.atEnd()) {
		QString line = in.readLine();
		
		//if there is a new way/node
		if(line.contains("<way") || line.contains("<node")) {
			// reset
			hnr.city="";
			hnr.country="";
			hnr.dupe=NULL;
			hnr.id=0;
			hnr.ignore=false;
			hnr.isHnr=false;
			hnr.isWay=false;
			hnr.lat=0;
			hnr.lon=0;
			hnr.name="";
			hnr.nodeId="";
			hnr.number="";
			hnr.postcode="";
			hnr.shop="";
			hnr.street="";
			
			QString id=line;
			// NOTE: QRegExp seems to be extremly slow, so we don't use .* here
			id.replace("\n", "");			//remove newline
			if(!line.contains("<way"))
				id.replace(QRegExp("<node id=[\"']"), "");	//remove unneeded information
			else {
				id.replace(QRegExp("<way id=[\"']"), "");
				hnr.isWay=true;
			}
			hnr.id=id.split(QRegExp("[\"']"))[0].toInt();
			
			if(line.contains("lat"))
				hnr.lat=line.split("lat")[1].split(QRegExp("[\"']"))[1].toDouble();
			
			if(line.contains("lon"))
				hnr.lon=line.split("lon")[1].split(QRegExp("[\"']"))[1].toDouble();
			
			if(line.contains("/>") && !hnr.isWay) { // no children
				insertNode(hnr);
			}
		
		// if there is the end of the way/node
		} else if( (line.contains("</way") || line.contains("</node")) ) {
			
			if(isComplete(hnr)) {
				pBinTree pHnr;
				pHnr = new binTree;
				housenumberToBinTree(hnr, pHnr);
				insert(pHnr, treeHousenumbers);
				pBinTree pHnr2;
				pHnr2 = new binTree;
				nodeToBinTree(hnr, pHnr2);
				insert(pHnr2, treeNodes);
			} else {
				if(line.contains("</node")) {
					insertNode(hnr);
				}
				//qDebug() << "There is something wrong with this element";
				//qDebug() << hnr.lat << hnr.lon << hnr.id << hnr.country << hnr.city << hnr.street << hnr.number << hnr.ignore;
			} // if(isComplete)
			
		} else if(line.contains("k=\"addr:") || line.contains("k='addr:")) {
			if(line.contains("addr:country")) {
				hnr.country=line.split(QRegExp("[\"']"))[3];
				hnr.isHnr=true;
			} else if(line.contains("addr:city")){
				hnr.city=line.split(QRegExp("[\"']"))[3];
				if(!bIgnoreCityHint) hnr.isHnr=true;
			} else if(line.contains("addr:postcode")){
				hnr.postcode=line.split(QRegExp("[\"']"))[3];
				hnr.isHnr=true;
			} else if(line.contains("addr:street")){
				hnr.street=line.split(QRegExp("[\"']"))[3];
				hnr.isHnr=true;
			//we use a given housename when there is no housenumber
			} else if( (line.contains("addr:housenumber")) || ((line.contains("addr:housename") && hnr.number=="")) ){
				hnr.number=line.split(QRegExp("[\"']"))[3];
				hnr.isHnr=true;
			//interpolation lines should be ignored
			} else if(line.contains("addr:interpolation")){
				hnr.ignore=true;
			} else if(line.contains("addr:housename") && hnr.name=="") {
				hnr.name=line.split(QRegExp("[\"']"))[3];
			}
		// later on, the duplicate house number check will ignore POIs with different shop/amenity/tourism tag
		} else if( line.contains("k=\"shop\"") || line.contains("k='shop'") ||
		           line.contains("k=\"amenity\"") || line.contains("k='amenity'") ||
		           line.contains("k='tourism'") || line.contains("k=\"tourism\"") ) {
			hnr.shop=line.split(QRegExp("[\"']"))[3];
		} else if( ( line.contains("k=\"name\"") || line.contains("k='name'") || line.contains("k=\"operator\"") || line.contains("k='operator'") ) && hnr.name=="") {
			hnr.name=line.split(QRegExp("[\"']"))[3];
		// ignore ways/nodes with fixme/note
		} else if( ( (line.contains("k=\"fixme\"", Qt::CaseInsensitive) || line.contains("k='fixme'", Qt::CaseInsensitive)) && bIgnoreFixme ) ||
		           ( (line.contains("k=\"note\"", Qt::CaseInsensitive) || line.contains("k='note'", Qt::CaseInsensitive)) && bIgnoreNote ) ||
		           (line.contains("power") && line.contains("sub_station")) ||
		           (line.contains("street_lamp"))
			)
		{
			hnr.ignore=true;
		}
		else if(line.contains("<nd") && hnr.nodeId=="") {
			QString ref=line.split(QRegExp("[\"']"))[1];
			hnr.nodeId=ref.right(1)+ref;
		}
		
		lineCount++;
		if(lineCount%10000==0) qDebug() << lineCount <<  now.elapsed()/1000 << "seconds";
		
		if(lineCount%100000==0 && lines>0) {
			qDebug() << 100.0*lineCount/lines << "%";
		}
		
	} //while(!in.atEnd())
	
	
	duplicatesFile.close();
	incompleteFile.close();
	brokenFile.close();
	
	qDebug() << "finished after" <<  now.elapsed()/1000 << "seconds";
	qDebug() << hnrCount+dupeCount << "housenumbers," << dupeCount << "dupes," << incompleteCount << "incomplete," << brokenCount << "broken";
}

bool isComplete(housenumber &hnr) {
	
	if(!hnr.isHnr || hnr.id==0) return false;
	hnrCount++;
	
	if(hnr.ignore) return false;
	
	if(hnr.isWay) {
		vGetLatLonForWay(hnr.lat, hnr.lon, hnr.nodeId, treeNodes);
	}
	
	if(hnr.lat==0 || hnr.lon==0) return false;
	
	if(hnr.postcode!="") {
		if(bCheckPostcodeNumber && hnr.postcode!=QString("%1").arg(hnr.postcode.toInt())) {
			brokenStream << qsGenerateBrokenPostcodeOutput(hnr);
			brokenCount++;
		} else if(iCheckPostcodeChars>-1 && hnr.postcode.length()!=iCheckPostcodeChars) {
			brokenStream << qsGenerateBrokenPostcodeOutput(hnr);
			brokenCount++;
		}
	}
	
	if(bCheckStreetSuffix && (hnr.street.endsWith("str") || hnr.street.contains("str.")) ) {
		brokenStream << qsGenerateBrokenStreetOutput(hnr);
		brokenCount++;
	}
	
	int missingCount=0;
	
	if(hnr.country=="") missingCount++;
	if(hnr.city=="") missingCount++;
	if(hnr.postcode=="") missingCount++;
	if(hnr.street=="") missingCount++;
	if(hnr.number=="") missingCount++;
	
	if(missingCount>0) {
		incompleteStream << qsGenerateIncompleteOutput(hnr, missingCount);
		incompleteCount++;
		
		if(hnr.country=="") hnr.country=qsAssumeCountry;
		if(hnr.city=="") hnr.city=qsAssumeCity;
		if(hnr.postcode=="") hnr.city=qsAssumePostcode;
		
		if(hnr.country=="" || hnr.city=="" || hnr.postcode=="" || hnr.street=="" || hnr.number=="") {
			return false;
		}
	}
	
	return true;
}

QString qsGenerateDupeOutput(pBinTree hnr) {
	QString link=qsGenerateLink(hnr);
	
	QString dupeLink=qsGenerateLink(hnr->dupe);
	
	return QString("%1\t%2\tDupe\t%3 %4 is dupe of %5\tpin.png\t16,16\t-8,-8\n")
	                .arg(hnr->lat,0,'f',8).arg(hnr->lon,0,'f',8).arg(link)
	                .arg(hnr->address).arg(dupeLink);
}

QString qsGenerateIncompleteOutput(housenumber hnr, int i) {
	QString link=qsGenerateLink(hnr);
	
	return QString("%1\t%2\tIncomplete\t%3 %4 %5 %6 %7 %8 is missing %9 pieces of address information\tpin.png\t16,16\t-8,-8\n")
	                .arg(hnr.lat,0,'f',8).arg(hnr.lon,0,'f',8).arg(link)
	                .arg(hnr.country).arg(hnr.city).arg(hnr.postcode).arg(hnr.street).arg(hnr.number)
	                .arg(i);
}

QString qsGenerateBrokenStreetOutput(housenumber hnr) {
	QString link=qsGenerateLink(hnr);
	
	return QString("%1\t%2\tBroken\t%3 %4 %5 %6 <b>%7</b> %8 has problematic street\tpin.png\t16,16\t-8,-8\n")
	                .arg(hnr.lat,0,'f',8).arg(hnr.lon,0,'f',8).arg(link)
	                .arg(hnr.country).arg(hnr.city).arg(hnr.postcode).arg(hnr.street).arg(hnr.number);
}

QString qsGenerateBrokenPostcodeOutput(housenumber hnr) {
	QString link=qsGenerateLink(hnr);
	
	return QString("%1\t%2\tBroken\t%3 %4 %5 <b>%6</b> %7 %8 has problematic postcode\tpin.png\t16,16\t-8,-8\n")
	                .arg(hnr.lat,0,'f',8).arg(hnr.lon,0,'f',8).arg(link)
	                .arg(hnr.country).arg(hnr.city).arg(hnr.postcode).arg(hnr.street).arg(hnr.number);
}

QString qsGenerateLink(housenumber hnr) {
	return QString("<a target=\"_blank\" href=\"http://www.openstreetmap.org/browse/%1/%2\">%2</a> (<a target=\"josmframe\" href=\"http://localhost:8111/load_and_zoom?left=%5&right=%6&top=%4&bottom=%3&select=%1%2\">JOSM</a>)")
	                .arg(hnr.isWay ? "way" : "node")
	                .arg(hnr.id)
	                .arg(hnr.lat-0.000001,0,'f',7).arg(hnr.lat+0.000001,0,'f',7)
	                .arg(hnr.lon-0.000001,0,'f',7).arg(hnr.lon+0.000001,0,'f',7);
}
QString qsGenerateLink(pBinTree hnr) {
	return QString("<a target=\"_blank\" href=\"http://www.openstreetmap.org/browse/%1/%2\">%2</a> (<a target=\"josmframe\" href=\"http://localhost:8111/load_and_zoom?left=%5&right=%6&top=%4&bottom=%3&select=%1%2\">JOSM</a>)")
	                .arg(hnr->isWay ? "way" : "node")
	                .arg(hnr->id)
	                .arg(hnr->lat-0.000001,0,'f',7).arg(hnr->lat+0.000001,0,'f',7)
	                .arg(hnr->lon-0.000001,0,'f',7).arg(hnr->lon+0.000001,0,'f',7);
}

void vGetLatLonForWay(double &lat, double &lon, QString ref, pBinTree tree) {
	if(tree==NULL) {
		qDebug() << "This should not happen :(" << ref;
	} else {
		if(ref < tree->address) {
			vGetLatLonForWay(lat, lon, ref, tree->left);
		} else if(ref > tree->address) {
			vGetLatLonForWay(lat, lon, ref, tree->right);
		} else {
			lat=tree->lat;
			lon=tree->lon;
		}
	}
}

void inorder(pBinTree &tree) {
	if(tree!=NULL) {
		inorder(tree->left);
		qDebug() << " " << tree->address;
		inorder(tree->right);
	}
}

void insert(pBinTree &element, pBinTree &tree) {
	if(tree==NULL) {
		tree=element;
		//inorder(treeHousenumbers);
		//qDebug() << "--end";
	} else {
		//if(treeHousenumbers!=NULL) qDebug() << (element.address < root->address) << (element.address > root->address) << (element.address == root->address) << element.address << root->address << treeHousenumbers->address;
		if(element->address < tree->address) {
			insert(element, tree->left);
		} else if(element->address > tree->address) {
			insert(element, tree->right);
		} else {
			qDebug() << "Dupe found!";
			if(lines>0) {
				qDebug() << 100.0*lineCount/lines << "%";
			}
			element->dupe=tree;
			duplicatesStream << qsGenerateDupeOutput(element);
			dupeCount++;
		}
	}
}

void insertNode(housenumber hnr) {
	pBinTree pHnr;
	pHnr = new binTree;
	nodeToBinTree(hnr, pHnr);
	insert(pHnr, treeNodes);
}

void housenumberToBinTree(housenumber hnr, pBinTree &node) {
	node->address=QString("%1 %2 %3 %4 %5 %6 %7")
	                .arg(hnr.country).arg(hnr.city).arg(hnr.postcode).arg(hnr.street)
	                .arg(hnr.number).arg(hnr.name).arg(hnr.shop);
	node->dupe=hnr.dupe;
	node->id=hnr.id;
	node->isWay=hnr.isWay;
	node->lat=hnr.lat;
	node->lon=hnr.lon;
	node->left=NULL;
	node->right=NULL;
}

void nodeToBinTree(housenumber hnr, pBinTree &node) {
	node->address=QString("%1").arg(hnr.id);
	node->address=node->address.right(1)+node->address; // the ids are sorted, but a binary tree does not want to get the input sorted
	node->id=hnr.id;
	node->lat=hnr.lat;
	node->lon=hnr.lon;
	node->left=NULL;
	node->right=NULL;
}
