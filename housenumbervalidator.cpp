/*
	v0.5-130207
	
	Copyright (C) 2012 Markus Brenneis
	
	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
	
	TODO
	TODOs in source
	support suburb! (854045017, 1461556529, SA)
	relations(?), check consistency of relation name + addr:street
	
*/

#include <iostream>
#include <QDebug>
#include <QFile>
#include <QList>
#include <QString>
#include <QStringList>
#include <QTime>

const double POSSIBLE_DUPE_TIME=0.1;

class HouseNumber;

typedef HouseNumber* pHouseNumber;

#include "HouseNumber.h"

using namespace std;

QString qsAssumeCountry="", qsAssumeCity="", qsAssumePostcode="", filename="input.osm";
bool bIgnoreFixme=true, bIgnoreNote=true, bIgnoreCityHint=false, bCheckPostcodeNumber=false, bCheckStreetSuffix=false, bLog=false;
int lines=9200594, lineCount=0, dupeCount=0, possibleDupeCount=0, hnrCount=0, incompleteCount=0, brokenCount=0, iCheckPostcodeChars=-1, i=0;

QTextStream duplicatesStream, incompleteStream, brokenStream, logStream;

pHouseNumber treeHousenumbers, treeIncomplete;

void findPossibleDupes(pHouseNumber &hnr);
void findInTree(pHouseNumber &hnr, pHouseNumber &tree);
void inorder(pHouseNumber &root);
void addToTreeHousenumbers(pHouseNumber &element, pHouseNumber &tree);
void addToTreeIncomplete(pHouseNumber &element, pHouseNumber &tree);

int main(int argc, const char* argv[]) {
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
// 			qDebug() << "  -iih    --ignore-city-hint        Objects which hava a addr:city tag (and no other addr:* tag) are not considered to be a house number, and thus are not listed as incomplete";
			qDebug() << "  -l      --log                     Create a log file";
			qDebug() << "  -l=N    --lines=N                 Set lines variable (for sensible progress information";
			qDebug() << "  -nif,   --not-ignore-fixme        do output ways/nodes which have a fixme tag";
			qDebug() << "  -nin,   --not-ignore-note         do output ways/nodes which have a note tag";
			qDebug() << "  -h      --help                    Print this help";
			qDebug() << "\ncompiled on" << __DATE__;
			return 0;
		}
		
		filename=QString(argv[1]);
		
		for(int i=2; i<argc; ++i){
			if(QString(argv[i])=="-nif" || QString(argv[i])=="--not-ignore-fixme") bIgnoreFixme=false;
			else if(QString(argv[i])=="-nin" || QString(argv[i])=="--not-ignore-note") bIgnoreNote=false;
			/*else if(QString(argv[i])=="-in" or QString(argv[i])=="--ignore-note") ignorenote=TRUE;
			else if(QString(argv[i])=="-npc" or QString(argv[i])=="--no-postcode-count") nopostcodecount=TRUE;
			else if(QString(argv[i])=="-ich" or QString(argv[i])=="--ignore-country-hint") ignorecountryhint=TRUE;*/
// 			else if(QString(argv[i])=="-iih" or QString(argv[i])=="--ignore-city-hint") bIgnoreCityHint=true;
			/*else if(QString(argv[i])=="-iph" or QString(argv[i])=="--ignore-postcode-hint") ignorepostcodehint=TRUE;
			else if(QString(argv[i])=="-ish" or QString(argv[i])=="--ignore-street-hint") ignorestreethint=TRUE;
			else if(QString(argv[i])=="-inh" or QString(argv[i])=="--ignore-number-hint") ignorenumberhint=TRUE;*/
			else if(QString(argv[i]).contains("-ac=") || QString(argv[i]).contains("--assume-country=")) qsAssumeCountry=QString(argv[i]).split("=")[1];
			else if(QString(argv[i]).contains("-ai=") || QString(argv[i]).contains("--assume-city=")) qsAssumeCity=QString(argv[i]).split("=")[1];
			else if(QString(argv[i]).contains("-ap=") || QString(argv[i]).contains("--assume-postcode=")) qsAssumePostcode=QString(argv[i]).split("=")[1];
			else if(QString(argv[i]).contains("--check-postcode-number") || QString(argv[i]).contains("-cpn")) bCheckPostcodeNumber=true;
			else if(QString(argv[i]).contains("--check-postcode-chars=") || QString(argv[i]).contains("-cpc=")) iCheckPostcodeChars=QString(argv[i]).split("=")[1].toInt();
			else if(QString(argv[i]).contains("--check-street-suffix") || QString(argv[i]).contains("-css")) bCheckStreetSuffix=true;
			else if(QString(argv[i])=="--log" || QString(argv[i])=="-l") bLog=true;
			else if(QString(argv[i]).contains("-l=") || QString(argv[i]).contains("--lines=")) lines=QString(argv[i]).split("=")[1].toInt();
			else {
				qDebug() <<  "unknown option " << argv[i];
				return(1);
			}
		}
	}
	
	treeHousenumbers=NULL;
// 	treeIncomplete=NULL;
	
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
	duplicatesStream << "lat\tlon\tid\ttype\tname\tcountry\tcity\tpostcode\tstreet\tnumber\thousename\tuid\tdupe_id\tdupe_type\tdupe_lat\tdupe_lon\tdupe_uid\tpossible_dupe\ttimestamp\n";
	
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
	brokenStream << "lat\tlon\tid\ttype\tbroken\tname\tcountry\tcity\tpostcode\tstreet\tnumber\thousename\teasyfix\tuid\ttimestamp\n";
	
	pHouseNumber hnr;
	
// 	if(lines==9200594) qDebug() << "NOTE: You have to set the 'lines=N' options by hand in order to get sensible progress information";
	if(!filename.endsWith(".hnr.osm")) qDebug() << "NOTE: Version 0.4+ supports nodes only, you should execute ' ./filter" << filename << "' first.";
	
	// loop through all lines
	while(!in.atEnd()) {
		QString line = in.readLine();
		
		//if there is a new node
		if(line.contains("<node")) {
			// reset
			hnr=new HouseNumber();
			
			QString id=line;
			// NOTE: QRegExp seems to be extremly slow, so we don't use .* here
			id.replace("\n", ""); //remove newline
			qint64 id64=id.split(QRegExp("[\"']"))[1].toLongLong();
			
			if(id64>999999999999900) {
				hnr->setIsWay(true);;
				hnr->setId(id64-1000000000000000);
			} else {
				hnr->setId(id64);
			}
			
			if(line.contains("lat"))
				hnr->setLat(line.split("lat")[1].split(QRegExp("[\"']"))[1].toDouble());
			
			if(line.contains("lon"))
				hnr->setLon(line.split("lon")[1].split(QRegExp("[\"']"))[1].toDouble());
			
			if(line.contains("user"))
				hnr->setUid(line.section("user",1).split(QRegExp("[\"']"))[1]);
			
			if(line.contains("timestamp")) {
				QStringList ts=line.section("timestamp",1).split(QRegExp("[\"']"))[1].split(QRegExp("[\\-T]"));
				hnr->setTimeStamp(ts[0].toInt(), ts[1].toInt(), ts[2].toInt());
			}
			
		// if there is the end of the node
		} else if(line.contains("</node")) {
			
			if(hnr->hasAddressInformation()) {
				// if we have a node with addr:postcode=11 it is broken though it is not considered to be a house number
				int broken=hnr->getBroken();
				if(broken!=0) {
					brokenStream << hnr->qsGenerateBrokenOutput();
					++brokenCount;
				}
				if(hnr->isHouseNumber()) {
					++hnrCount;
					if(broken==0 && !hnr->getIgnore()) {
						if(hnr->isComplete()) {
							addToTreeHousenumbers(hnr, treeHousenumbers);
						} else {
							addToTreeIncomplete(hnr, treeIncomplete);
							++incompleteCount;
						}
					}
				}
			}
			
		} else if(line.contains("k=\"addr:") || line.contains("k='addr:")) {
			if(line.contains("addr:country\"") || line.contains("addr:country'")) {
				hnr->setCountry(line.split(QRegExp("[\"']"))[3]);
// 				hnr.isHnr=true;
			} else if(line.contains("addr:city\"") || line.contains("addr:city'")) {
				hnr->setCity(line.split(QRegExp("[\"']"))[3]);
// 				if(!bIgnoreCityHint) hnr.isHnr=true;
			} else if(line.contains("addr:postcode\"") || line.contains("addr:postcode'")) {
				hnr->setPostcode(line.split(QRegExp("[\"']"))[3]);
// 				hnr.isHnr=true;
			} else if(line.contains("addr:street\"") || line.contains("addr:street'")) {
				hnr->setStreet(line.split(QRegExp("[\"']"))[3]);
// 				hnr.isHnr=true;
			} else if(line.contains("addr:housenumber\"") || line.contains("addr:housenumber'")) {
				hnr->setNumber(line.split(QRegExp("[\"']"))[3]);
// 				hnr.isHnr=true;
			} else if(line.contains("addr:housename\"") || line.contains("addr:housename'")) {
				hnr->setHousename(line.split(QRegExp("[\"']"))[3]);
// 				hnr.isHnr=true;
			} else if(line.contains("addr:suburb\"") || line.contains("addr:suburb'")) {
				hnr->setSuburb(line.split(QRegExp("[\"']"))[3]);
			//interpolation lines should be ignored
			} else if(line.contains("addr:interpolation")) {
				hnr->setIgnore(true);
			}
		// later on, the duplicate house number check will ignore POIs with different shop/amenity/tourism tag
		} else if( line.contains("k=\"shop\"") || line.contains("k='shop'") ||
		           line.contains("k=\"amenity\"") || line.contains("k='amenity'") ||
		           line.contains("k='tourism'") || line.contains("k=\"tourism\"") ) {
			hnr->setShop(line.split(QRegExp("[\"']"))[3]);
		} else if( line.contains("k=\"name\"") || line.contains("k='name'") || line.contains("k=\"operator\"") || line.contains("k='operator'") ) {
			hnr->setName(line.split(QRegExp("[\"']"))[3]);
		// ignore nodes with fixme/note
		} else if( ( (line.contains("k=\"fixme\"", Qt::CaseInsensitive) || line.contains("k='fixme'", Qt::CaseInsensitive)) && bIgnoreFixme ) ||
		           ( (line.contains("k=\"note\"", Qt::CaseInsensitive) || line.contains("k='note'", Qt::CaseInsensitive)) && bIgnoreNote ) ||
		           (line.contains("power") && line.contains("sub_station")) ||
		           (line.contains("street_lamp"))
			)
		{
			hnr->setIgnore(true);
		}
		
		++lineCount;
		if(lineCount%10000==0) qDebug() << lineCount <<  now.elapsed()/1000 << "seconds";
		
		if(lineCount%100000==0 && lines>0) {
			qDebug() << 100.0*lineCount/lines*(1-POSSIBLE_DUPE_TIME) << "%";
		}
		
	} //while(!in.atEnd())
	
	qDebug() << "dupes" << dupeCount << "incomplete" << incompleteCount;
	
	findPossibleDupes(treeIncomplete);
	
	duplicatesFile.close();
	incompleteFile.close();
	brokenFile.close();
	
	qDebug() << "finished after" <<  now.elapsed()/1000 << "seconds";
	qDebug() << hnrCount << "housenumbers," << dupeCount << "dupes," << incompleteCount << "incomplete," << brokenCount << "broken," << possibleDupeCount << "possible_dupes";
	
	QFile logFile("log.txt");
	logFile.remove();
	if (!logFile.open(QIODevice::WriteOnly | QIODevice::Text)) {
		qDebug() << "couldn't open log.txt";
		file.close();
		return(-2);
	}
	logStream.setDevice(&logFile);
	logStream.setCodec("UTF-8");
	logStream << "finished after " <<  now.elapsed()/1000 << " seconds" << endl;
	logStream << hnrCount << " housenumbers, " << dupeCount << " dupes, " << incompleteCount << " incomplete, " << brokenCount << " broken, " << possibleDupeCount << " possible_dupes" << endl;
	
	return 0;
}

void inorder(pHouseNumber &tree) {
	if(tree!=NULL) {
		inorder(tree->left);
// 		qDebug() << " " << tree->address; TODO
		inorder(tree->right);
	}
}

void findPossibleDupes(pHouseNumber &hnr) {
	if(hnr!=NULL) {
		if(i%100==0) {
			qDebug() << 100.0*i/incompleteCount*POSSIBLE_DUPE_TIME+(1-POSSIBLE_DUPE_TIME)*100.0 << "%";
		}
		findInTree(hnr, treeHousenumbers);
		if(hnr->dupe==NULL) {
			findInTree(hnr, treeIncomplete);
		}
		if(hnr->dupe!=NULL) {
			duplicatesStream << hnr->qsGenerateDupeOutput(true);
			++dupeCount;
			++possibleDupeCount;
			qDebug() << "possible dupe found";
		}
		++i;
		findPossibleDupes(hnr->left);
		findPossibleDupes(hnr->right);
	}
}

void findInTree(pHouseNumber &hnr, pHouseNumber &tree) {
	if(tree!=NULL && hnr->dupe==NULL) {
		if(hnr->getNumber().toLower()>tree->getNumber().toLower()) {
			findInTree(hnr, tree->right);
		} else if(hnr->getNumber().toLower()<tree->getNumber().toLower()) {
			findInTree(hnr, tree->left);
		} else {
			if(hnr->getStreet().toLower()>tree->getStreet().toLower()) {
				findInTree(hnr, tree->right);
			} else if(hnr->getStreet().toLower()<tree->getStreet().toLower()) {
				findInTree(hnr, tree->left);
			} else {
				findInTree(hnr, tree->left);
				// do not say that a house number is a dupe of itself and prevent mutual accusations
				if(hnr->isSameAddress(*tree) && !(hnr->isSameNode(*tree)) && tree->dupe==NULL) {
					hnr->dupe=tree;
				}
				findInTree(hnr, tree->right);
			}
		}
	}
}

/*!
 * inserts @param element into the binary tree @param tree, unless there is a dupe (dupes will be written to dupes.txt)
 */
void addToTreeHousenumbers(pHouseNumber &element, pHouseNumber &tree) {
	if(tree==NULL) {
		tree=element;
		//inorder(treeHousenumbers);
		//qDebug() << "--end";
	} else {
		//if(treeHousenumbers!=NULL) qDebug() << (element.address < root->address) << (element.address > root->address) << (element.address == root->address) << element.address << root->address << treeHousenumbers->address;
		if(element->isLessThanAddress(*tree)) {
			addToTreeHousenumbers(element, tree->left);
		} else if(element->isGreaterThanAddress(*tree)) {
			addToTreeHousenumbers(element, tree->right);
		} else {
			qDebug() << "Dupe found!";
			if(lines>0) {
				qDebug() << 100.0*lineCount/lines*(1-POSSIBLE_DUPE_TIME) << "%";
			}
			element->dupe=tree;
			duplicatesStream << element->qsGenerateDupeOutput();
			++dupeCount;
		}
	}
}

/*!
 * inserts @param element into the binary tree @param tree
 */
void addToTreeIncomplete(pHouseNumber &element, pHouseNumber &tree) {
	if(tree==NULL) {
		tree=element;
	} else {
		if(element->isLessThanNode(*tree)) {
			addToTreeIncomplete(element, tree->left);
		} else if(element->isGreaterThanNode(*tree)) {
			addToTreeIncomplete(element, tree->right);
		} else {
			qDebug() << "[E] We should not get here";
			qDebug() << element->qsGenerateBrokenOutput();
		}
	}
}
