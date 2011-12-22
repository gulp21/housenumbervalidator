/*
	v111221
	
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

struct housenumber;

struct housenumber {
	double lat, lon;
	int id;
	bool fixme, isWay, dupeIsWay;
	QString name, shop, number, street, postcode, city, country;
	housenumber* dupe;
};

QString qsAssumeCountry="", qsAssumeCity="", qsAssumePostcode="", filename="input.hnr.osm";
bool bIgnoreFixme=true;

QList<housenumber> qlHousenumbers, qlDupes, qlNodes;

bool bFindDupe(housenumber &hnr);
bool isComplete(housenumber hnr);
QString qsGenerateOutput(housenumber hnr);
void vGetLatLonForWay(housenumber &hnr, int ref);

int main(int argc, const char* argv[]){ 
	QTime now;
	now.start();
        
	if(argc>1) {
		if(QString(argv[1])=="--help" || QString(argv[1])=="-h") {
			qDebug() << "Usage: ./housenumbervalidator [FILENAME.hnr.osm [OPTIONS]]\n";
			qDebug() << "Options:";
			qDebug() << "  -ac=XX  --assume-country=XX   When a housenumber does not have a addr:country value, it is set to XX";
			qDebug() << "  -aci=XX --assume-city=XX";
			qDebug() << "  -ap=XX  --assume-postcode=XX";
			qDebug() << "  -nif,   --not-ignore-fixme    do output ways/nodes which have a fixme tag";
			qDebug() << "  -h      --help                Print this help";
			
			return 0;
		}
		
		filename=QString(argv[1]);
		
		for(int i=2; i<argc; i++){
			if(QString(argv[i])=="-nif" || QString(argv[i])=="--not-ignore-fixme") bIgnoreFixme=false;
			/*else if(QString(argv[i])=="-in" or QString(argv[i])=="--ignore-note") ignorenote=TRUE;
			else if(QString(argv[i])=="-npc" or QString(argv[i])=="--no-postcode-count") nopostcodecount=TRUE;
			else if(QString(argv[i])=="-ich" or QString(argv[i])=="--ignore-country-hint") ignorecountryhint=TRUE;
			else if(QString(argv[i])=="-iih" or QString(argv[i])=="--ignore-city-hint") ignorecityhint=TRUE;
			else if(QString(argv[i])=="-iph" or QString(argv[i])=="--ignore-postcode-hint") ignorepostcodehint=TRUE;
			else if(QString(argv[i])=="-ish" or QString(argv[i])=="--ignore-street-hint") ignorestreethint=TRUE;
			else if(QString(argv[i])=="-inh" or QString(argv[i])=="--ignore-number-hint") ignorenumberhint=TRUE;*/
			else if(QString(argv[i]).contains("-ac=") || QString(argv[i]).contains("--assume-country=")) qsAssumeCountry=QString(argv[i]).split("=")[1];
			else if(QString(argv[i]).contains("-aci=") || QString(argv[i]).contains("--assume-city=")) qsAssumeCountry=QString(argv[i]).split("=")[1];
			else if(QString(argv[i]).contains("-ap=") || QString(argv[i]).contains("--assume-postcode=")) qsAssumeCountry=QString(argv[i]).split("=")[1];
// 			else if(QString(argv[i]).contains("-wo=")) dupesperosm=QString(argv[i]).mid(4).toInt();
// 			else if(QString(argv[i]).contains("--write-osm=")) dupesperosm=QString(argv[i]).mid(12).toInt();
			else {
				qDebug() <<  "unknown option " << argv[i];
				return(1);
			}
		}
	}
	
	if(!filename.endsWith(".hnr.osm")) {
		qDebug("The filename should end with .hnr.osm\nPlease do NOT try to load LARGE files!\nUse ./filer to create a .hnr.osm file which contains only housenumbers.");
		return -9;
	}
	
	//open input file
	QFile file(filename);
	if (!file.open(QIODevice::ReadOnly | QIODevice::Text)) {
		qDebug() << "couldn't open" << filename;
		return(2);
	}
	
	QFile duplicatesFile("dupes.txt");
	duplicatesFile.remove();
	if (!duplicatesFile.open(QIODevice::WriteOnly | QIODevice::Text)) {
		qDebug() << "couldn't open dupes.txt";
		file.close();
		return(3);
	}
	QTextStream duplicatesStream(&duplicatesFile);
	
	QDomDocument doc("osm");
	
	bool namespaceProcessing=false; QString errorMsg; int errorLine, errorColumn;
	if(!doc.setContent(&file, namespaceProcessing, &errorMsg, &errorLine, &errorColumn )){
		file.close();
		qDebug() << "[W] Problem reading file" << file.fileName();
		qDebug() << "     Line" << errorLine << "Column" << errorColumn << errorMsg;
		return -2;
	}
	file.close();
	
	QDomElement docElem = doc.documentElement();
	if(docElem.tagName()!="osm") {
		qDebug() << "[W] " << file.fileName() << "is no osm-file";
		qDebug() << "     docElem.tagName is" << docElem.tagName();
		return -3;
	}
	
	for(int l=0; l<2; l++) {
		
		QDomNodeList dnlNodes=docElem.elementsByTagName(l==0 ? "node" : "way");
		
		if(dnlNodes.length()==0) {
			qDebug() << "No nodes?";
		}
		
		for(unsigned int i=0; i<dnlNodes.length(); i++) {
			
			QDomNode n=dnlNodes.at(i).firstChild();
			
			housenumber hnr;
			hnr.id=dnlNodes.at(i).toElement().attribute("id","0").toInt();
			hnr.fixme=false;
			if(l==0) {
				hnr.lat=dnlNodes.at(i).toElement().attribute("lat","0").toDouble();
				hnr.lon=dnlNodes.at(i).toElement().attribute("lon","0").toDouble();
				if(n.isNull()) qlNodes.append(hnr);
				hnr.isWay=false;
			} else {
				hnr.lat=-1;
				hnr.isWay=true;
			}
			
			while(!n.isNull()) {
				
				QDomElement e=n.toElement(); // try to convert the node to an element
				
				if(!e.isNull()) {
					
					if(e.tagName()=="tag") {
						if(e.attribute("k",qsAssumeCountry)=="addr:country") {
							hnr.country=e.attribute("v","");
						} else if(e.attribute("k",qsAssumeCity)=="addr:city") {
							hnr.city=e.attribute("v","");
						} else if(e.attribute("k",qsAssumePostcode)=="addr:postcode") {
							hnr.postcode=e.attribute("v","");
						} else if(e.attribute("k","")=="addr:street") {
							hnr.street=e.attribute("v","");
						} else if(e.attribute("k","")=="addr:housenumber") {
							hnr.number=e.attribute("v","");
						} else if(e.attribute("k","")=="shop"
						          || e.attribute("k","")=="amenity") {
							hnr.shop=e.attribute("v",hnr.shop);
						} else if(e.attribute("k","")=="name"
						          || e.attribute("k","")=="addr:housename") {
							hnr.name=e.attribute("v",hnr.name);
						} else if(e.attribute("k","").toLower()=="fixme") {
							hnr.fixme=true;
						}
					} else if(e.tagName()=="nd" && hnr.lat==-1) {
						vGetLatLonForWay(hnr, e.attribute("ref","0").toInt());
					}
					
				} // if(!e.isNull())
				
				n=n.nextSibling();
				
			} // while(!n.isNull())
			
			if(isComplete(hnr)) {
				if(bFindDupe(hnr)) {
					qDebug() << 100*i/dnlNodes.length() << "- Dupe found!";
					qlDupes.append(hnr);
				} else {
					qlHousenumbers.append(hnr);
				}
			} else {
				//qDebug() << "There is something wrong with this element";
				//qDebug() << hnr.lat << hnr.lon << hnr.id << hnr.country << hnr.city << hnr.street << hnr.number << hnr.fixme;
			}
			
		} // for(dnlNodes)
	} // for(nodes/ways)
	
	duplicatesStream << "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
	for(int i=0; i<qlDupes.length(); i++) {
		qDebug() << qsGenerateOutput(qlDupes[i]);
		duplicatesStream << qsGenerateOutput(qlDupes[i]);
	}
	
	duplicatesFile.close();
	
	qDebug() << "finished after" <<  now.elapsed()/1000 << "seconds";
	qDebug() << qlHousenumbers.length() << "housenumbers," << qlDupes.length() << "dupes";
}

bool bFindDupe(housenumber &hnr) {
	for(int i=0; i<qlHousenumbers.count(); i++) {
		if(qlHousenumbers[i].number==hnr.number && qlHousenumbers[i].street==hnr.street &&
		   qlHousenumbers[i].city==hnr.city && qlHousenumbers[i].postcode==hnr.postcode &&
		   qlHousenumbers[i].name==hnr.name &&
		   qlHousenumbers[i].country==hnr.country && qlHousenumbers[i].shop==hnr.shop) {
			hnr.dupe=&qlHousenumbers[i];
			return true;
		}
	}
	return false;
}

bool isComplete(housenumber hnr) {
	if(hnr.id==0 || hnr.lat==0 || hnr.lon==0 || hnr.country=="" || hnr.city=="" || hnr.postcode=="" || hnr.number=="" || (hnr.fixme==true && bIgnoreFixme) ) return false;
	return true;
}

QString qsGenerateOutput(housenumber hnr) {
	QString link=QString("<a href=\"http://www.openstreetmap.org/browse/%1/%2\">%2</a> (<a href=\"http://localhost:8111/load_and_zoom?left=%5&right=%6&top=%4&bottom=%3&select=%1%2\">JOSM</a>)")
	                .arg(hnr.isWay ? "way" : "node")
	                .arg(hnr.id)
	                .arg(hnr.lat-0.000001,0,'f',7).arg(hnr.lat+0.000001,0,'f',7)
	                .arg(hnr.lon-0.000001,0,'f',7).arg(hnr.lon+0.000001,0,'f',7);
	
	QString dupeLink=QString("<a href=\"http://www.openstreetmap.org/browse/%1/%2\">%2</a> (<a href=\"http://localhost:8111/load_and_zoom?left=%5&right=%6&top=%4&bottom=%3&select=%1%2\">JOSM</a>)")
	                .arg(hnr.dupe->isWay ? "way" : "node")
	                .arg(hnr.dupe->id)
	                .arg(hnr.dupe->lat-0.000001,0,'f',7).arg(hnr.dupe->lat+0.000001,0,'f',7)
	                .arg(hnr.dupe->lon-0.000001,0,'f',7).arg(hnr.dupe->lon+0.000001,0,'f',7);
	
	return QString("%1\t%2\tDupe\t%3 %4 %5 %6 %7 %8 is dupe of %9 \tpin.png\t16,16\t-8,-8\n")
	                .arg(hnr.lat,0,'f',8).arg(hnr.lon,0,'f',8).arg(link)
	                .arg(hnr.country).arg(hnr.city).arg(hnr.postcode).arg(hnr.street)
	                .arg(hnr.number).arg(dupeLink);
}

void vGetLatLonForWay(housenumber &hnr, int ref) {
	for(int i=0; i<qlNodes.count(); i++) {
		if(qlNodes[i].id==ref) {
			hnr.lat=qlNodes[i].lat;
			hnr.lon=qlNodes[i].lon;
		}
	}
}
