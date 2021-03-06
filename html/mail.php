<?php
	include("../connect.php");
	
	$skip=10; // number of entries which are skipped, so that everyone (probably) gets an object from another area
	
	$i=0; // number of elements in the mailContents array
	
	$VERY_NEAR_THRESHOLD=0.00002;
	
	mysql_set_charset("utf8");
	
	$mails=mysql_query("SELECT * FROM mails") or die ("MySQL-Error: ".mysql_error());
	
	$subscribers=mysql_num_rows($mails);
	
	echo $subscribers." subscribers<br/>";
	
	$probs=mysql_query("SELECT * FROM problematic
	                    WHERE corrected=0 AND easyfix=1
	                    AND timestamp < NOW() - INTERVAL 4 DAY")
	                    or die ("MySQL-Error: ".mysql_error());
	
	echo "Found ".mysql_num_rows($probs)." problems<br/>";
	
	while(mysql_num_rows($probs) < $subscribers*$skip+$skip && $skip!=0) {
		echo $skip." that's not enough...<br/>";
		--$skip;
	}
	
	if($skip>1)
		$startIndex=rand(0,mysql_num_rows($probs)-$subscribers*$skip);
	else
		$startIndex=0;
	
	echo "startIndex ".$startIndex."<br/>";
	
	$skipped=$skip;
	
	while(($prob=mysql_fetch_assoc($probs)) && $i<$subscribers) {
		
		if($startIndex-->0) continue;
		
		if($skipped!=$skip) {
			++$skipped;
			continue;
		}
		
		$skipped=0;
		
		$table="";
		
		if(trim($prob['name'])!="") $table.="Name\t".$prob['name']."\n";
		
		if($prob['broken'] & 1) $style='*'; else $style='';
		if($prob['country']!="") $table.=$style."addr:country\t".$prob['country'].$style."\n";
		
		if($prob['broken'] & 2) $style='*'; else $style='';
		if($prob['city']!="") $table.=$style."addr:city\t".$prob['city'].$style."\n";
		
		if($prob['broken'] & 4) $style='*'; else $style='';
		if($prob['postcode']!="") $table.=$style."addr:postcode\t".$prob['postcode'].$style."\n";
		
		if($prob['broken'] & 8) $style='*'; else $style='';
		if($prob['street']!="") $table.=$style."addr:street\t".$prob['street'].$style."\n";
		
		if($prob['broken'] & 16) $style='*'; else $style='';
		if($prob['number']!="") $table.=$style."addr:housenumber\t".$prob['number'].$style."\n";
		
		if($prob['broken'] & 32) $style='*'; else $style='';
		if($prob['housename']!="") $table.=$style."addr:housename\t".$prob['housename'].$style."\n";
		
		if($prob['type']==1) {
			$type="way";
		} else {
			$type="node";
		}
		
		$link="OSM: http://www.openstreetmap.org/browse/".$type."/".$prob['id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($prob['lon']-0.001)."&right=".($prob['lon']+0.001)."&top=".($prob['lat']+0.001)."&bottom=".($prob['lat']-0.001)."&select=".$type.$prob['id']."\nPotlatch 2: http://www.openstreetmap.org/edit?zoom=18&".$type."=".$prob['id']."&editor=potlatch2\niD: http://www.openstreetmap.org/edit?zoom=18&".$type."=".$prob['id']."&editor=iD";
		
		$mailContents[$i]=
			"Problematisch\n"
			.$link."\n"
			.$table
			."\n";
		
		++$i;
	}
	
	$dupes=mysql_query("SELECT * FROM dupes
	                    WHERE corrected=0 AND possible_dupe=0
	                    AND (lat-dupe_lat)<$VERY_NEAR_THRESHOLD and (lon-dupe_lon)<$VERY_NEAR_THRESHOLD
	                    and (lat-dupe_lat)>-$VERY_NEAR_THRESHOLD and (lon-dupe_lon)>-$VERY_NEAR_THRESHOLD")
	                    or die ("MySQL-Error: ".mysql_error());
	
	echo "Found ".mysql_num_rows($dupes)." dupes<br/>";
	
	$skip=10;
	
	if(mysql_num_rows($dupes) < $subscribers*$skip-$subscribers+$i+$skip)
		die("that's not enough...");
	
	$startIndex=rand(0,mysql_num_rows($dupes)-($subscribers*$skip-$subscribers+$i));
	
	echo "startIndex ".$startIndex."<br/>";
	
	$skipped=$skip;
	
	while(($dupe=mysql_fetch_assoc($dupes)) && $i<$subscribers*2) {
		
		if($startIndex-->0) continue;
		
		if($skipped!=$skip) {
			++$skipped;
			continue;
		}
		
		$skipped=0;
		
		$table="";
		
		if(trim($dupe['name'])!="") $table.="Name\t".$dupe['name']."\n";
		if($dupe['country']!="") $table.="addr:country\t".$dupe['country']."\n";
		if($dupe['city']!="") $table.="addr:city\t".$dupe['city']."\n";
		if($dupe['postcode']!="") $table.="addr:postcode\t".$dupe['postcode']."\n";
		if($dupe['street']!="") $table.="addr:street\t".$dupe['street']."\n";
		if($dupe['number']!="") $table.="addr:housenumber\t".$dupe['number']."\n";
		if($dupe['housename']!="") $table.="addr:housename\t".$dupe['housename']."\n";
		
		if($dupe['type']==1) {
			$type="way";
		} else {
			$type="node";
		}
		
		if($dupe['dupe_type']==1) {
			$type_dupe="way";
		} else {
			$type_dupe="node";
		}
		
		$link="OSM: http://www.openstreetmap.org/browse/".$type."/".$dupe['id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($dupe['lon']-0.001)."&right=".($dupe['lon']+0.001)."&top=".($dupe['lat']+0.001)."&bottom=".($dupe['lat']-0.001)."&select=".$type.$dupe['id']."\nPotlatch 2: http://www.openstreetmap.org/edit?zoom=18&".$type."=".$dupe['id']."&editor=potlatch2\niD: http://www.openstreetmap.org/edit?zoom=18&".$type."=".$dupe['id']."&editor=id";
		
		$link.="\nOSM: http://www.openstreetmap.org/browse/".$type_dupe."/".$dupe['dupe_id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($dupe['dupe_lon']-0.001)."&right=".($dupe['dupe_lon']+0.001)."&top=".($dupe['dupe_lat']+0.001)."&bottom=".($dupe['dupe_lat']-0.001)."&select=".$type_dupe.$dupe['dupe_id']."\nPotlatch 2: http://www.openstreetmap.org/edit?zoom=18&".$type_dupe."=".$dupe['dupe_id']."&editor=potlatch2\niD: http://www.openstreetmap.org/edit?zoom=18&".$type_dupe."=".$dupe['dupe_id']."&editor=id";
		
		echo "i ".$i." skipped ".$skipped." skip ".$skip." subsc+skip-subsc+i "+($subscribers*$skip-$subscribers+$i)." subs*skip-subsk+i+skip ".($subscribers*$skip-$subscribers+$i+$skip)."\n";
		
		$mailContents[$i]=
			"Duplikat\n"
			.$link."\n"
			.$table
			."\n\n";
		
		++$i;
	}
	
	for($j=0; $mail=mysql_fetch_assoc($mails); ++$j) {
		mail($mail['mail'], "Ein korrigierter Fehler am Tag", "Wie wär's heute mit diesem kleinen Fehler:\n\n".$mailContents[$j]."Und wenn noch etwas Zeit ist:\n".$mailContents[$j+$subscribers]."--\nBei Problemen oder Fragen oder wenn Sie sich abmelden wollen, besuchen Sie bitte http://gulp21.bplaced.net/osm/housenumbervalidator und nutzen Sie die angegebenen Kontaktmöglichkeiten oder den Link \"Ein korrigierter Fehler am Tag\" zum Abmelden.", "Content-Type: text/plain; charset=\"utf-8\"\nFrom: housenumbervalidator <support.gulp21@googlemail.com>");
		
		$first=explode("\n",$mailContents[$j]);
		$second=explode("\n",$mailContents[$j+$subscribers]);
		
		echo "<br/>".$mail['mail']."<br/>"
		     .$first[0]." ".$first[1]."<br/>"
		     .$second[0]." ".$second[1]."<br/>";
	}
	
?>
