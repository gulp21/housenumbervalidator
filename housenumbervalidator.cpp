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
#include <QTime>

using namespace std;

struct housenumber {
	double lat, lon;
	int id, dupeId;
	QString name, shop, housenumber, street, postcode, city, country;
};

QString filename="input.osm";

QList<housenumber> qlHousenumbers, qlDupes;

bool bFindDupe(housenumber &hnr);

int main(int argc, const char* argv[]){ 
	QTime now;
	now.start();
	// function compare(a, b){
	// 	a=explode(" ", a);
	// 	b=explode(" ", b);
	// 	//compare postcodes
	// 	if(a[5] == b[5]) return 0;
	// 	return (a[5] < b[5]) ? -1 : 1;
	// }
	if(argc>1) {
		filename=QString(argv[1]);
	}
	
	//open input file
	QFile file(filename);
	if (!file.open(QIODevice::ReadOnly | QIODevice::Text)) {
		qDebug() << "couldn't open" << filename;
		return(2);
	}
	QTextStream in(&file);
	
	QFile duplicatesFile("dupes.txt");
	duplicatesFile.remove();
	if (!duplicatesFile.open(QIODevice::WriteOnly | QIODevice::Text)) {
		qDebug() << "couldn't open dupes.txt";
		file.close();
		return(3);
	}
	
	QDomDocument doc("qcfx");
	
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
	
	QDomNodeList dnlNodes=docElem.elementsByTagName("node");
	QDomNodeList dnlWays=docElem.elementsByTagName("way");
	
	for(unsigned int i=0; i<dnlNodes.length(); i++) {
		
		QDomNode n=dnlNodes.at(i).firstChild();
		
		housenumber hnr;
		hnr.id=dnlNodes.at(i).toElement().attribute("id","0").toInt();
		hnr.lat=dnlNodes.at(i).toElement().attribute("lat","0").toInt();
		hnr.lon=dnlNodes.at(i).toElement().attribute("lon","0").toInt();
		
		while(!n.isNull()) {
			
			QDomElement e=n.toElement(); // try to convert the node to an element
			
			if(!e.isNull()) {
				
				if(e.tagName()=="tag") {
					if(e.attribute("k","")=="addr:country") {
						hnr.country=e.attribute("v","");
					} else if(e.attribute("k","")=="addr:city") {
						hnr.city=e.attribute("v","");
					} else if(e.attribute("k","")=="addr:postcode") {
						hnr.postcode=e.attribute("v","");
					} else if(e.attribute("k","")=="addr:street") {
						hnr.street=e.attribute("v","");
					} else if(e.attribute("k","")=="addr:housenumber") {
						hnr.housenumber=e.attribute("v","");
					}
				}
				
			} // if(!e.isNull())
			
			n = n.nextSibling();
			
		} // while(!n.isNull())
		
		if(hnr.id!=0 && hnr.housenumber!="") {
			if(bFindDupe(hnr)) {
				qDebug() << "Found dupe!";
				qlDupes.append(hnr);
			} else {
				qlHousenumbers.append(hnr);
			}
		} else {
			//qDebug() << "There is some thing wrong with this element" << hnr.id << hnr.housenumber;
		}
		
	} // for(dnlNodes)
	
	for(int i=0; i<qlDupes.length(); i++) {
		qDebug() << qlDupes[i].id << qlDupes[i].city << qlDupes[i].housenumber << "id dupe of" << qlDupes[i].dupeId;
	}
	
	qDebug() <<  "finished after" <<  now.elapsed()/1000 << "seconds";
}

bool bFindDupe(housenumber &hnr) {
	for(int i=0; i<qlHousenumbers.count(); i++) {
		if(qlHousenumbers[i].housenumber==hnr.housenumber && qlHousenumbers[i].street==hnr.street &&
		   qlHousenumbers[i].city==hnr.city && qlHousenumbers[i].postcode==hnr.postcode && qlHousenumbers[i].country==hnr.country) {
			hnr.dupeId=qlHousenumbers[i].id;
			return true;
		}
	}
	return false;
}
