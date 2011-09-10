<?php

/*
	v110616
	
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
	- support for database(?)
*/

$now=time();

#set this to FALSE if wc isn't available or you do not want to have progress messages
if (PHP_OS=="Linux") $wordcount=TRUE;

function compare($a, $b){
	$a=explode(" ", $a);
	$b=explode(" ", $b);
	#compare postcodes
	if ($a[5] == $b[5]) return 0;
	return ($a[5] < $b[5]) ? -1 : 1;
}

$filename="input.osm";
$nopostcodecount=FALSE;
$ignorefixme=FALSE;
$ignorenote=FALSE;
$ignorecountryhint=FALSE;
$ignorecityhint=FALSE;
$ignorepostcodehint=FALSE;
$ignorestreethint=FALSE;
$ignorenumberhint=FALSE;
$assumecountry=-1;
$dupesperosm=-1;

if($argc>1){
	$filename=$argv[1];
	
	for($i=2; $i<$argc; $i++){
		if($argv[$i]=="-if" or $argv[$i]=="--ignore-fixme") $ignorefixme=TRUE;
		elseif($argv[$i]=="-in" or $argv[$i]=="--ignore-note") $ignorenote=TRUE;
		elseif($argv[$i]=="-npc" or $argv[$i]=="--no-postcode-count") $nopostcodecount=TRUE;
		elseif($argv[$i]=="-ich" or $argv[$i]=="--ignore-country-hint") $ignorecountryhint=TRUE;
		elseif($argv[$i]=="-iih" or $argv[$i]=="--ignore-city-hint") $ignorecityhint=TRUE;
		elseif($argv[$i]=="-iph" or $argv[$i]=="--ignore-postcode-hint") $ignorepostcodehint=TRUE;
		elseif($argv[$i]=="-ish" or $argv[$i]=="--ignore-street-hint") $ignorestreethint=TRUE;
		elseif($argv[$i]=="-inh" or $argv[$i]=="--ignore-number-hint") $ignorenumberhint=TRUE;
		elseif(strstr($argv[$i],"-ac=") or strstr($argv[$i],"--assume-country=")) $assumecountry=substr($argv[$i], -2);
		elseif(strstr($argv[$i],"-wo=")) $dupesperosm=substr($argv[$i], 4);
		elseif(strstr($argv[$i],"--write-osm=")) $dupesperosm=substr($argv[$i], 12);
		else { print "unknown option ".$argv[$i]."\n"; exit;}
	}
}

#open input file
$file = fopen($filename, "r") or die ("No File.");

$duplicatesFile = fopen("dupes.txt", 'w');

$city="NOCITY";
$street="NOSTREET";
$postcode="NOPOSTCODE";
$number="NONUMBER";
$country="NOCOUNTRY";
$interpolation=FALSE;
$name="NONAME";
$shop="NOSHOP";
$fixme=FALSE;
$note=FALSE;
$addr=FALSE;

$nocity=array();
$nocountry=array();
$nostreet=array();
$nonumber=array();
$nopostcode=array();
$addresses=array();
$ids=array();
$somethingmissing=FALSE;

$postcodes=array();
$postcodecount=array();

$currentline=0;
if($wordcount){
	print "Lines: ";
	$lines=exec("wc -l ".$filename);
	print $lines."\n";
}

# loop through all lines, stop when there's the first relation (these are usally saved at the end of the file)
while($line = fgets($file, 1024) and !strstr($line, '<relation')) {
	$currentline++;
	#if there is a new way/node
	if (strstr($line, '<way') or strstr($line, '<node')) {
		$node=str_replace("\n", "", $line);				#remove newline
		$node=preg_replace("!.*<node.* id=[\"']!", "node/", $node);	#remove unneeded information
		$node=preg_replace("!.*<way.* id=[\"']!", "way/", $node);
		$node=preg_replace("![\"'].*!", "", $node);
		$country="NOCOUNTRY";						#reset variables
		$city="NOCITY";
		$postcode="NOPOSTCODE";
		$street="NOSTREET";
		$number="NONUMBER";
		$interpolation=FALSE;
		$name="NONAME";
		$shop="NOSHOP";
		$fixme=FALSE;
		$note=FALSE;
		$addr=FALSE;
		
		$somethingmissing=FALSE;
	
	#if there is the end of the way/node and we have an address and no fixme/note is set [option] and it isn't an interpolation line, check entry
	} elseif ( (strstr($line, '</way') or strstr($line, '</node')) and $addr and !($fixme and $ignorefixme) and !($note and $ignorenote) and !$interpolation ) {
		$output=$node." ".$country." ".$postcode." ".$city." ".$street." ".$number." ".$name." ".$shop." \n";
		
		#check if addr:* information is missing
		if ($country=="NOCOUNTRY") {
			#save in array
			$nocountry[]=$output;
			#we shouldn't check for duplicate house numbers when the address information is incomplete unless -ac=xx is used
			if($assumecountry==-1){
				$somethingmissing=TRUE;
			} else {
				$country=$assumecountry;
				$output=$node." ".$country." ".$postcode." ".$city." ".$street." ".$number." ".$name." ".$shop." \n";
			}
		}
		if ($city=="NOCITY") {
			$nocity[]=$output;
			$somethingmissing=TRUE;
		}
		if ($postcode=="NOPOSTCODE") {
			$nopostcode[]=$output;
			$somethingmissing=TRUE;
		#count postcodes in order to find possible typos (this might only be useful when using a small osm file)
		} elseif (!$nopostcodecount) {
			if(in_array($postcode, $postcodes)) {				#if the postcode is already in the array...
				$index=array_search($postcode,$postcodes);		#...find its index...
				$postcodecount[$index]++;				#...and increase count
			} else {							#...otherwise add new postcode to array
				$postcodes[]=$postcode;
				$postcodecount[]=1;
			}
		}
		if ($street=="NOSTREET") {
			$nostreet[]=$output;
			$somethingmissing=TRUE;
		}
		if ($number=="NONUMBER") {
			$nonumber[]=$output;
			$somethingmissing=TRUE;
		}
		#check if there is an address more than once (ignoring ways/nodes with name/operator, see below)
		if (!$somethingmissing and !($name=="NONAME" and $shop!="NOSHOP")) {
			$s=explode(' ',$output,2);							#split at first space (after ID)
			$index=array_search($s[1],$addresses);						#find address in array
			if($index!==FALSE) {								#if the address is already in the array...
				$out=$s[0]." dupe of ".$ids[$index]." ".$addresses[$index];		#...output it and the dupe
				print $out;
				if($wordcount) {
					print round(100/$lines*$currentline,3)."% - ".$currentline."/".$lines." - ";
					print time()-$now." seconds\n";
				}
				fwrite($duplicatesFile, $out);
			} else {									#...otherwise add new address to array
				$addresses[]=$s[1];
				$ids[]=$s[0];
			}
		}
	} elseif (strstr($line, 'k="addr:') or strstr($line, 'k=\'addr:')) {
		if (strstr($line, 'addr:country')) {
			$country=str_replace("\n", "", $line);
			$country=preg_replace("!.*=[\"']!", "", $country);
			$country=preg_replace("![\"'].*!", "", $country);
			if(!$ignorecountryhint) $addr=TRUE;
		} elseif (strstr($line, 'addr:city')) {
			$city=str_replace("\n", "", $line);
			$city=preg_replace("!.*=[\"']!", "", $city);
			$city=preg_replace("![\"'].*!", "", $city);
			if(!$ignorecityhint) $addr=TRUE;
		} elseif (strstr($line, 'addr:postcode')) {
			$postcode=str_replace("\n", "", $line);
			$postcode=preg_replace("!.*=[\"']!", "", $postcode);
			$postcode=preg_replace("![\"'].*!", "", $postcode);
			if(!$ignorepostcodehint) $addr=TRUE;
		} elseif (strstr($line, 'addr:street')) {
			$street=str_replace("\n", "", $line);
			$street=preg_replace("!.*=[\"']!", "", $street);
			$street=preg_replace("![\"'].*!", "", $street);
			if(!$ignorestreethint) $addr=TRUE;
		//we use a given housename when there is no housenumber given
		} elseif ( (strstr($line, 'addr:housenumber')) or (stristr($line, 'addr:housename') and $name=="NONAME") )  {
			$number=str_replace("\n", "", $line);
			$number=preg_replace("!.*=[\"']!", "", $number);
			$number=preg_replace("![\"'].*!", "", $number);
			if(!$ignorenumberhint) $addr=TRUE;
		//interpolation lines should be ignored
		} elseif (strstr($line, 'addr:interpolation')) {
			$interpolation=TRUE;
		}
	//later on, the duplicate house number check will ignore POIs without name (or operator) and those with different shop/amenity/tourism tag
	} elseif ( strstr($line, 'k="shop"') or strstr($line, 'k="amenity"') or strstr($line, 'k=\'shop\'') or strstr($line, 'k=\'amenity\'')  or strstr($line, 'k=\'tourism\'') or strstr($line, 'k=\'tourism\'') ) {
		$shop=str_replace("\n", "", $line);
		$shop=preg_replace("!.*=[\"']!", "", $shop);
		$shop=preg_replace("![\"'].*!", "", $shop);
	} elseif (strstr($line, 'k="name"') or strstr($line, 'k=\'name\'') or (strstr($line, 'k="operator"') and $name=="NONAME") ) {
		$name=str_replace("\n", "", $line);
		$name=preg_replace("!.*=[\"']!", "", $name);
		$name=preg_replace("![\"'].*!", "", $name);
	//[option] ignore ways/nodes with fixme/note
	} elseif ( (stristr($line, 'k="fixme"') or stristr($line, 'k=\'fixme\'')) and $ignorefixme) {
		$fixme=TRUE;
	} elseif ( (stristr($line, 'k="note') or stristr($line, 'k=\'note')) and $ignorenote) {
		$note=TRUE;
	}
}

fclose($duplicatesFile);

if($dupesperosm!=-1){
	mkdir("dupes");
	
	$wikitable = fopen("dupes/wikitable.txt", 'w');
	
	$count=0;
	$filenumber=0;
	$contents=array();
	
	$lines=file("dupes.txt");
	
	#sort according to postcode
	usort($lines, "compare");
	
	array_push($lines,"EOF    ");
	$numberoflines=count($lines);
	
	foreach($lines as $line){
		$count++;
		
		#when we have enough lines or near EOF
		if($count%$dupesperosm==0 or $line=="EOF    "){
			#remove duplicates
			$contents=array_unique($contents);
			#write data to file
			$file = fopen("dupes/dupes_".$filenumber.".osm", 'w');
			fwrite($file, "<?xml version='1.0' encoding='UTF-8'?>\n");
			fwrite($file, "<osm version='0.6' generator='housenumbervalidator'>\n");
			foreach ($contents as $line1){
				fwrite($file, $line1);
			}
			fwrite($file, "</osm>");
			fclose($file);
			
			fwrite($wikitable, "|[http://gulp21.bplaced.net/osm/dupes/dupes_$filenumber.osm $filenumber]\n|".$s[5]."\n|\n|\n|-\n");
			
			echo round(100*$count/$numberoflines,1)."% - ".$count."/".$numberoflines." - File #".$filenumber."\n";
			
			#clear array
			$contents=array();
			$filenumber++;
		}
		
		$s=explode(" ", $line);
		
		$s[0]=preg_replace("!way\/!", "<way version=\"1\" id=\"", $s[0]);
		$s[0]=preg_replace("!node\/!", "<node version=\"1\" id=\"", $s[0]);
		$contents[]=$s[0]."\" lat=\"1\" lon=\"1\"/>\n";
		
		$s[3]=preg_replace("!way\/!", "<way version=\"1\" id=\"", $s[3]);
		$s[3]=preg_replace("!node\/!", "<node version=\"1\" id=\"", $s[3]);
		$contents[]=$s[3]."\" lat=\"1\" lon=\"1\"/>\n";
	}
	
	fclose($wikitable);
}

$incompleteFile = fopen("incomplete.txt", 'w');

#write the incomplete addresses to incomplete.txt
print "---\n";
print "NOCOUNTRY\n";
foreach($nocountry AS $entry) {
	print $entry;
	fwrite($incompleteFile, "NOCOUNTRY: ".$entry);
}
print "---\n";
print "NOCITY\n";
foreach($nocity AS $entry) {
	print $entry;
	fwrite($incompleteFile, "NOCITY: ".$entry);
}
print "---\n";
print "NOPOSTCODE\n";
foreach($nopostcode AS $entry) {
	print $entry;
	fwrite($incompleteFile, "NOPOSTCODE: ".$entry);
}
print "---\n";
print "NOSTREET\n";
foreach($nostreet AS $entry) {
	print $entry;
	fwrite($incompleteFile, "NOSTREET: ".$entry);
}
print "---\n";
print "NONUMBER\n";
foreach($nonumber AS $entry) {
	print $entry;
	fwrite($incompleteFile, "NONUMBER: ".$entry);
}
#this isn't written to a file since IDs aren't saved, thus this is not useful when checking big files
#the output might be sent through sort -n -k 2 -t x TODO sort & save
if(!$nopostcodecount){
	print "---\n";
	print "POSTCODES\n";
	foreach (array_combine($postcodes, $postcodecount) as $postcode => $count) {
	print $postcode." x".$count."\n";
	}
}

fclose($incompleteFile);

print "\nfinished after ";
print time()-$now." seconds\n";

?>