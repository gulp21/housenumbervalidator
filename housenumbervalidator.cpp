/*
	v110616c
	
	Usage
	php housenumbervalidator.php [FILENAME [OPTION]...]
	
	FILNEMAE: default: input.osm, must be given if an option is used
	
	Options:
	-wo=n, --write-osm=n		save the dupes in osm-files with n dupes in the folder dupes
						WARNING: existing files might be overwritten!
						NOTE: osm-file must be updated using the editor
	-if, --ignore-fixme		do not output ways/nodes which have a fixme tag
	-in, --ignore-note		do not output ways/nodes which have a note tag
	-npc, --no-postcode-count	do not count postcodes
	-ac=xx, --assume-country=xx	for ways/nodes w/o addr:country, assume that it's xx
	-ich, --ignore-country-hint	do not think `that's a house number`, when addr:country is given
	-iih, --ignore-city-hint	do not think `that's a house number`, when addr:city is given
	-iph, --ignore-postcode-hint	do not think `that's a house number`, when addr:postcode is given
	-ish, --ignore-street-hint	do not think `that's a house number`, when addr:street is given
	-inh, --ignore-number-hint	do not think `that's a house number`, when addr:housenumber or addr:housename is given
	
	
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
	
	
	TODO
	- Anleitung ^ anpassen
*/

#include <iostream>
#include <iomanip>
#include <QString>
#include <QList>
#include <QFile>
#include <QTextStream>
#include <QRegExp>
#include <QTime>

using namespace std;

QString filename="input.osm";
bool nopostcodecount=FALSE, ignorefixme=FALSE, ignorenote=FALSE, ignorecountryhint=FALSE, ignorecityhint=FALSE, ignorepostcodehint=FALSE, ignorestreethint=FALSE, ignorenumberhint=FALSE, wordcount=FALSE;
QString assumecountry="-1";
int dupesperosm=-1;

long currentline=0;
int lines=45214099;

QString node, city="NOCITY", street="NOSTREET", postcode="NOPOSTCODE", number="NONUMBER", country="NOCOUNTRY", name="NONAME", shop="NOSHOP";
bool interpolation=FALSE, fixme=FALSE, note=FALSE, addr=FALSE;

QList<QString> nocity, nocountry, nostreet, nonumber, nopostcode, addresses, ids;
bool somethingmissing=FALSE;

QList<QString> postcodes;
QList<int> postcodecount;

int main(int argc, const char* argv[]){ 
	
	setprecision(3);

	QTime now;
	now.start();

	// set this to FALSE if you do not want to have progress messages
	wordcount=TRUE;

	// function compare(a, b){
	// 	a=explode(" ", a);
	// 	b=explode(" ", b);
	// 	//compare postcodes
	// 	if(a[5] == b[5]) return 0;
	// 	return (a[5] < b[5]) ? -1 : 1;
	// }



	if(argc>1){
		filename=argv[1];
		
		for(int i=2; i<argc; i++){
			if(QString(argv[i])=="-if" or QString(argv[i])=="--ignore-fixme") ignorefixme=TRUE;
			else if(QString(argv[i])=="-in" or QString(argv[i])=="--ignore-note") ignorenote=TRUE;
			else if(QString(argv[i])=="-npc" or QString(argv[i])=="--no-postcode-count") nopostcodecount=TRUE;
			else if(QString(argv[i])=="-ich" or QString(argv[i])=="--ignore-country-hint") ignorecountryhint=TRUE;
			else if(QString(argv[i])=="-iih" or QString(argv[i])=="--ignore-city-hint") ignorecityhint=TRUE;
			else if(QString(argv[i])=="-iph" or QString(argv[i])=="--ignore-postcode-hint") ignorepostcodehint=TRUE;
			else if(QString(argv[i])=="-ish" or QString(argv[i])=="--ignore-street-hint") ignorestreethint=TRUE;
			else if(QString(argv[i])=="-inh" or QString(argv[i])=="--ignore-number-hint") ignorenumberhint=TRUE;
			else if(QString(argv[i]).contains("-ac=") or QString(argv[i]).contains("--assume-country=")) assumecountry=QString(argv[i]).right(2);
			else if(QString(argv[i]).contains("-wo=")) dupesperosm=QString(argv[i]).mid(4).toInt();
			else if(QString(argv[i]).contains("--write-osm=")) dupesperosm=QString(argv[i]).mid(12).toInt();
			else { cout <<  "unknown option " << argv[i] << "\n"; return(1);}
		}
	}
	
	//open input file
	QFile file(filename);
	if (!file.open(QIODevice::ReadOnly | QIODevice::Text)) { cout << "couldn't open" << filename.toStdString() << "\n"; return(2);}
	QTextStream in(&file);
	
	QFile duplicatesFile("dupes.txt");
	duplicatesFile.remove();
	if (!duplicatesFile.open(QIODevice::WriteOnly | QIODevice::Text)) { cout << "couldn't open dupes.txt\n"; file.close(); return(3);}
	QTextStream duplicates(&duplicatesFile);
	
	
	if(wordcount){
		cout <<  "Lines: ";
// 		lines=exec("wc -l ".filename);
		cout << lines << "\n";
	}
	
	// loop through all lines
	while(!in.atEnd()) {
		QString line = in.readLine();
		currentline++;
		//if there is a new way/node
		if(line.contains("<way") or line.contains("<node")){
			node=line;
			// NOTE: QRegExp seems to be extremly slow, so I don't use .* here
			node.replace("\n", "");					//remove newline
			node.replace(QRegExp("<node id=[\"']"), "node/");	//remove unneeded information
			node.replace(QRegExp("<way id=[\"']"), "way/");
			node.replace(QRegExp("[\"'] .*"), "");
			country="NOCOUNTRY";					//reset variables
			city="NOCITY";
			postcode="NOPOSTCODE";
			street="NOSTREET";
			number="NONUMBER";
			interpolation=FALSE;
			name="NONAME";
			shop="NOSHOP";
			fixme=FALSE;
			note=FALSE;
			addr=FALSE;
			
			somethingmissing=FALSE;
		
// 		if there is the end of the way/node and we have an address and no fixme/note is set [option] and it isn't an interpolation line, check entry
		} else if( (line.contains("</way") or line.contains("</node")) and addr and !(fixme and ignorefixme) and !(note and ignorenote) and !interpolation ){
			QString output=QString("%1 %2 %3 %4 %5 %6 %7 %8\n").arg(node).arg(country).arg(postcode).arg(city).arg(street).arg(number).arg(name).arg(shop);
			
			//check if addr:* information is missing
			if(country=="NOCOUNTRY"){
				//save in array
				nocountry.append(output);
				//we shouldn't check for duplicate house numbers when the address information is incomplete unless -ac=xx is used
				if(assumecountry=="-1"){
					somethingmissing=TRUE;
				} else {
					country=assumecountry;
					output=QString("%1 %2 %3 %4 %5 %6 %7 %8\n").arg(node).arg(country).arg(postcode).arg(city).arg(street).arg(number).arg(name).arg(shop);
				}
			}
			if(city=="NOCITY"){
				nocity.append(output);
				somethingmissing=TRUE;
			}
			if(postcode=="NOPOSTCODE"){
				nopostcode.append(output);
				somethingmissing=TRUE;
			//count postcodes in order to find possible typos (this might only be useful when using a small osm file)
			} else if(!nopostcodecount){
				if(postcodes.contains(postcode)){		//if the postcode is already in the array...
					int index=postcodes.indexOf(postcode);	//...find its index...
					postcodecount[index]+=1;		//...and increase count
				} else {					//...otherwise add new postcode to array
					postcodes.append(postcode);
					postcodecount.append(1);
				}
			}
			if(street=="NOSTREET"){
				nostreet.append(output);
				somethingmissing=TRUE;
			}
			if(number=="NONUMBER"){
				nonumber.append(output);
				somethingmissing=TRUE;
			}
			//check if there is an address more than once (ignoring ways/nodes with name/operator, see below)
			if(!somethingmissing and !(name=="NONAME" and shop!="NOSHOP")){
// 				WARNING s=explode(' ',output,2);		//split at first space (after ID)
				output=QString("%1 %2 %3 %4 %5 %6 %7\n").arg(country).arg(postcode).arg(city).arg(street).arg(number).arg(name).arg(shop);
				int index=addresses.indexOf(output);		//find address in array
				if(index!=-1){					//if the address is already in the array...
					QString out=QString("%1 dupe of %2 %3").arg(node).arg(ids[index]).arg(addresses[index]);	//...output it and the dupe
					cout << out.toStdString();
					if(wordcount){
						cout <<  100/lines*currentline << "% - " << currentline << "/" << lines << " - " <<  now.elapsed()/1000 << " seconds\n";
					}
					duplicates << out;
				} else {					//...otherwise add new address to array
					addresses.append(output);
					ids.append(node);
				}
			}
		} else if(line.contains("k=\"addr:") or line.contains("k='addr:")){
			if(line.contains("addr:country")){
				country=line;
				country.replace("\n", "");
				country.replace(QRegExp(".*=[\"']"), "");
				country.replace(QRegExp("[\"'].*"), "");
				if(!ignorecountryhint) addr=TRUE;
			} else if(line.contains("addr:city")){
				city=line;
				city.replace("\n", "");
				city.replace(QRegExp(".*=[\"']"), "");
				city.replace(QRegExp("[\"'].*"), "");
				if(!ignorecityhint) addr=TRUE;
			} else if(line.contains("addr:postcode")){
				postcode=line;
				postcode.replace("\n", "");
				postcode.replace(QRegExp(".*=[\"']"), "");
				postcode.replace(QRegExp("[\"'].*"), "");
				if(!ignorepostcodehint) addr=TRUE;
			} else if(line.contains("addr:street")){
				street=line;
				street.replace("\n", "");
				street.replace(QRegExp(".*=[\"']"), "");
				street.replace(QRegExp("[\"'].*"), "");
				if(!ignorestreethint) addr=TRUE;
			//we use a given housename when there is no housenumber given
			} else if( (line.contains("addr:housenumber")) or (line.contains("addr:housename") and name=="NONAME") ){
				number=line;
				number.replace("\n", "");
				number.replace(QRegExp(".*=[\"']"), "");
				number.replace(QRegExp("[\"'].*"), "");
				if(!ignorenumberhint) addr=TRUE;
			//interpolation lines should be ignored
			} else if(line.contains("addr:interpolation")){
				interpolation=TRUE;
			}
		//later on, the duplicate house number check will ignore POIs without name (or operator) and those with different shop/amenity/tourism tag
		} else if( line.contains("k=\"shop\"") or line.contains("k=\"amenity\"") or line.contains("k='shop'") or line.contains("k='amenity'") or line.contains("k='tourism'") or line.contains("k='tourism'") ){
			shop=line;
			shop.replace("\n", "");
			shop.replace(QRegExp(".*=[\"']"), "");
			shop.replace(QRegExp("[\"'].*"), "");
		} else if(line.contains("k=\"name\"") or line.contains("k='name'") or (line.contains("k=\"operator\"") and name=="NONAME") ){
			name=line;
			name.replace("\n", "");
			name.replace(QRegExp(".*=[\"']"), "");
			name.replace(QRegExp("[\"'].*"), "");
// 		[option] ignore ways/nodes with fixme/note
		} else if( (line.contains("k=\"fixme\"", Qt::CaseInsensitive) or line.contains("k='fixme'", Qt::CaseInsensitive)) and ignorefixme){
			fixme=TRUE;
		} else if( (line.contains("k=\"note", Qt::CaseInsensitive) or line.contains("k='note", Qt::CaseInsensitive)) and ignorenote){
			note=TRUE;
		}
	}

	duplicatesFile.close();
	file.close();
	
/*	<<-php-only (quick enough)->>
	if(dupesperosm!=-1){
		mkdir("dupes");
		
		wikitable = fopen("dupes/wikitable.txt", 'w');
		
		count=0;
		filenumber=0;
		contents=array();
		
// 		lines=file("dupes.txt");
		
		//sort according to postcode
		usort(lines, "compare");
		
		array_push(lines,"EOF    ");
		numberoflines=count(lines);
		
		QString line, line1;
// 		foreach(line, lines){
			count++;
			
			//when we have enough lines or near EOF
			if(count%dupesperosm==0 or line=="EOF    "){
				//remove duplicates
				contents=array_unique(contents);
				//write data to file
// 				file = fopen("dupes/dupes_".filenumber.".osm", 'w');
				fwrite(file, "<?xml version='1.0' encoding='UTF-8'?>\n");
				fwrite(file, "<osm version='0.6' generator='housenumbervalidator'>\n");
				
// 				foreach (line1, contents){
// 					fwrite(file, line1);
// 				}
// 				fwrite(file, "</osm>");
// 				fclose(file);
				
// 				fwrite(wikitable, "|[http://gulp21.bplaced.net/osm/dupes/dupes_filenumber.osm filenumber]\n|".s[5]."\n|\n|\n|-\n");
				
				echo round(100*count/numberoflines,1)."% - ".count."/".numberoflines." - File //".filenumber."\n";
				
				//clear array
				contents=array();
				filenumber++;
			}
			
			s=explode(" ", line);
			
			s[0]=preg_replace("way\/"", "<way version=\"1\" id=\"", s[0]);
			s[0]=preg_replace("node\/"", "<node version=\"1\" id=\"", s[0]);
			contents[]=s[0]."\" lat=\"1\" lon=\"1\"/>\n";
			
			s[3]=preg_replace("way\/"", "<way version=\"1\" id=\"", s[3]);
			s[3]=preg_replace("node\/"", "<node version=\"1\" id=\"", s[3]);
			contents[]=s[3]."\" lat=\"1\" lon=\"1\"/>\n";
// 		}
		
		fclose(wikitable);
	}<---->
*/
	
	QFile incompleteFile("incomplete.txt");
	incompleteFile.remove();
	incompleteFile.open(QIODevice::WriteOnly);
	if (!incompleteFile.isOpen()) { cout << "couldn't open incomplete.txt\n"; return(4);}
	QTextStream incomplete(&incompleteFile);
	
	//write the incomplete addresses to incomplete.txt
	cout <<  "---\n";
	cout <<  "NOCOUNTRY\n";
	QString entry;
	foreach(entry, nocountry){
		cout << entry.toStdString();
		incomplete << "NOCOUNTRY: " << entry;
	}
	cout <<  "---\n";
	cout <<  "NOCITY\n";
	foreach(entry, nocity){
		cout << entry.toStdString();
		incomplete << "NOCITY: " << entry;
	}
	cout <<  "---\n";
	cout <<  "NOPOSTCODE\n";
	foreach(entry, nopostcode){
		cout << entry.toStdString();
		incomplete << "NOPOSTCODE: " << entry;
	}
	cout <<  "---\n";
	cout <<  "NOSTREET\n";
	foreach(entry, nostreet){
		cout << entry.toStdString();
		incomplete << "NOSTREET: " << entry;
	}
	cout <<  "---\n";
	cout <<  "NONUMBER\n";
	foreach(entry, nonumber){
		cout << entry.toStdString();
		incomplete << "NONUMBER: " << entry;
	}
	//this isn't written to a file since IDs aren't saved, thus this is not useful when checking big files
	//the output might be sent through sort -n -k 2 -t x TODO sort & save
	if(!nopostcodecount){
		cout <<  "---\n";
		cout <<  "POSTCODES\n";
		int n=postcodecount.size();
		cout << n;
		for(int i=0;i<n;i++){
			cout << postcodes[i].toStdString() << " x" << postcodecount[i] << "\n";
		}
	}

	incompleteFile.close();

	cout <<  "\nfinished after " <<  now.elapsed()/1000 << " seconds\n";
}