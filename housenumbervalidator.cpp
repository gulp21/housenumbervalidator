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
	bool ignore, isWay;
	QString name, shop, number, street, postcode, city, country;
	housenumber* dupe;
};

QString qsAssumeCountry="", qsAssumeCity="", qsAssumePostcode="", filename="input.osm";
bool bIgnoreFixme=true;
int lines=55366689, lineCount=0, dupeCount=0;

QList<housenumber> qlHousenumbers, /*qlDupes,*/ qlNodes;

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
	QTextStream duplicatesStream(&duplicatesFile);
	duplicatesStream << "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
	
	housenumber hnr;
	
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
			hnr.isWay=false;
			hnr.lat=0;
			hnr.lon=0;
			hnr.name="";
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
				qlNodes.append(hnr);
			}

		// if there is the end of the way/node
		} else if( (line.contains("</way") || line.contains("</node")) ) {
			
			if(!hnr.ignore) {
				if(isComplete(hnr)) {
					if(bFindDupe(hnr)) {
						qDebug() << "Dupe found!";
						if(lines>0) {
							qDebug() << 100.0*lineCount/lines << "%";
						}
						duplicatesStream << qsGenerateOutput(hnr);
						dupeCount++;
					} else {
						qlHousenumbers.append(hnr);
					}
				} else {
					if(line.contains("</node")) {
						qlNodes.append(hnr);
					}
					//qDebug() << "There is something wrong with this element";
					//qDebug() << hnr.lat << hnr.lon << hnr.id << hnr.country << hnr.city << hnr.street << hnr.number << hnr.ignore;
				}
			}
			
		} else if(line.contains("k=\"addr:") || line.contains("k='addr:")) {
			if(line.contains("addr:country")) {
				hnr.country=line.split(QRegExp("[\"']"))[3];
			} else if(line.contains("addr:city")){
				hnr.city=line.split(QRegExp("[\"']"))[3];
			} else if(line.contains("addr:postcode")){
				hnr.postcode=line.split(QRegExp("[\"']"))[3];
			} else if(line.contains("addr:street")){
				hnr.street=line.split(QRegExp("[\"']"))[3];
			//we use a given housename when there is no housenumber
			} else if( (line.contains("addr:housenumber")) || ((line.contains("addr:housename") && hnr.name=="")) ){
				hnr.number=line.split(QRegExp("[\"']"))[3];
			//interpolation lines should be ignored
			} else if(line.contains("addr:interpolation")){
				hnr.ignore=true;
			} else if(line.contains("addr:housename") && hnr.name=="") {
				hnr.name=line.split(QRegExp("[\"']"))[3];
			}
		// later on, the duplicate house number check will ignore POIs without name (or operator) and those with different shop/amenity/tourism tag
		} else if( line.contains("k=\"shop\"") || line.contains("k=\"amenity\"") || line.contains("k='shop'") or line.contains("k='amenity'") or line.contains("k='tourism'") || line.contains("k='tourism'") ) {
			hnr.shop=line.split(QRegExp("[\"']"))[3];
		} else if( ( line.contains("k=\"name\"") || line.contains("k='name'") || line.contains("k=\"operator\"") || line.contains("k='operator'") ) && hnr.name=="") {
			hnr.name=line.split(QRegExp("[\"']"))[3];
		// ignore ways/nodes with fixme/note
		} else if( (line.contains("k=\"fixme\"", Qt::CaseInsensitive) || line.contains("k='fixme'", Qt::CaseInsensitive)) && bIgnoreFixme) {
			hnr.ignore=true;
		}
		else if(line.contains("<nd") && hnr.lat==0) {
			QString ref=line.split(QRegExp("[\"']"))[1];
			vGetLatLonForWay(hnr, ref.toInt());
		}
		
		lineCount++;
		
		if(lineCount%100000==0 && lines>0) {
			qDebug() << 100.0*lineCount/lines << "%";
		}
		
	} //while(!in.atEnd())
	
	
	duplicatesFile.close();
	
	qDebug() << "finished after" <<  now.elapsed()/1000 << "seconds";
	qDebug() << qlHousenumbers.length() << "housenumbers," << dupeCount << "dupes";
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
	if(hnr.id==0 || hnr.lat==0 || hnr.lon==0 || hnr.country=="" || hnr.city=="" || hnr.postcode=="" || hnr.street=="" || hnr.number=="" || hnr.ignore) return false;
	return true;
}

QString qsGenerateOutput(housenumber hnr) {
	QString link=QString("<a target=\"_blank\" href=\"http://www.openstreetmap.org/browse/%1/%2\">%2</a> (<a target=\"josmframe\" href=\"http://localhost:8111/load_and_zoom?left=%5&right=%6&top=%4&bottom=%3&select=%1%2\">JOSM</a>)")
	                .arg(hnr.isWay ? "way" : "node")
	                .arg(hnr.id)
	                .arg(hnr.lat-0.000001,0,'f',7).arg(hnr.lat+0.000001,0,'f',7)
	                .arg(hnr.lon-0.000001,0,'f',7).arg(hnr.lon+0.000001,0,'f',7);
	
	QString dupeLink=QString("<a target=\"_blank\" href=\"http://www.openstreetmap.org/browse/%1/%2\">%2</a> (<a target=\"josmframe\" href=\"http://localhost:8111/load_and_zoom?left=%5&right=%6&top=%4&bottom=%3&select=%1%2\">JOSM</a>)")
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
