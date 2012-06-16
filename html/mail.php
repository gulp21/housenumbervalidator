<?php
	include("../connect.php");
	
	mysql_set_charset("utf8");
	
	$mails=mysql_query("SELECT * FROM mails") or die ("MySQL-Error: ".mysql_error());
	
	$subscribers=mysql_num_rows($mails);
	
	echo $subscribers." subscribers<br/>";
	
	$probs=mysql_query("SELECT * FROM problematic WHERE corrected=0 AND (`broken` & 39 OR `street` REGEXP '.*str\.? ?[0-9].*' OR `street` REGEXP '\<.*')") or die ("MySQL-Error: ".mysql_error());
	
	echo "Found ".mysql_num_rows($probs)." Ps<br/>";
	
	if(mysql_num_rows($probs) < $subscribers*10+10)
		die("that's not enough...");
	
	$i=-rand(0,mysql_num_rows($probs)-$subscribers*10);
	
	echo "i ".$i."<br/>";
	
	while($prob=mysql_fetch_assoc($probs)) {
		
		if($i++<0) continue;
		
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
		if($prob['number']!="") $table.=$style."addr:number\t".$prob['number'].$style."\n";
		
		if($prob['broken'] & 32) $style='*'; else $style='';
		if($prob['housename']!="") $table.=$style."addr:housename\t".$prob['housename'].$style."\n";
		
		if($prob['type']==1) {
			$type="way";
		} else {
			$type="node";
		}
		
		$link="OSM: http://www.openstreetmap.org/browse/".$type."/".$prob['id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($prob['lon']-0.001)."&right=".($prob['lon']+0.001)."&top=".($prob['lat']+0.001)."&bottom=".($prob['lat']-0.001)."&select=".$type.$prob['id']."\nPotlatch 2: http://www.openstreetmap.org/edit?zoom=18&".$type."=".$prob['id']."&editor=potlatch2";
		
		$problems[$i]=
			"Problematisch\n"
			.$link."\n"
			.$table
			."\n";
		
		if($i==$subscribers*10) break;
	}
	
	$dupes=mysql_query("SELECT * FROM dupes WHERE corrected=0 AND possible_dupe=0") or die ("MySQL-Error: ".mysql_error());
	
	echo "Found ".mysql_num_rows($dupes)." Ds<br/>";
	
	if(mysql_num_rows($dupes) < $subscribers*10+10)
                die("that's not enough...");
	
	$i=-rand(0,mysql_num_rows($dupes)-$subscribers*10);
	
	echo "i ".$i."<br/>";
	
	while($dupe=mysql_fetch_assoc($dupes)) {
		
		if($i++<0) continue;
		
		$table="";
		
		if(trim($dupe['name'])!="") $table.="Name\t".$dupe['name']."\n";
		if($dupe['country']!="") $table.="addr:country\t".$dupe['country']."\n";
		if($dupe['city']!="") $table.="addr:city\t".$dupe['city']."\n";
		if($dupe['postcode']!="") $table.="addr:postcode\t".$dupe['postcode']."\n";
		if($dupe['street']!="") $table.="addr:street\t".$dupe['street']."\n";
		if($dupe['number']!="") $table.="addr:number\t".$dupe['number']."\n";
		if($dupe['housename']!="") $table.="addr:number\t".$dupe['housename']."\n";
		
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
		
		$link="OSM: http://www.openstreetmap.org/browse/".$type."/".$dupe['id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($dupe['lon']-0.001)."&right=".($dupe['lon']+0.001)."&top=".($dupe['lat']+0.001)."&bottom=".($dupe['lat']-0.001)."&select=".$type.$dupe['id']."\nPotlatch 2: http://www.openstreetmap.org/edit?zoom=18&".$type."=".$dupe['id']."&editor=potlatch2";
		
		$link.="\nOSM: http://www.openstreetmap.org/browse/".$type_dupe."/".$dupe['dupe_id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($dupe['dupe_lon']-0.001)."&right=".($dupe['dupe_lon']+0.001)."&top=".($dupe['dupe_lat']+0.001)."&bottom=".($dupe['dupe_lat']-0.001)."&select=".$type_dupe.$dupe['dupe_id']."\nPotlatch 2: http://www.openstreetmap.org/edit?zoom=18&".$type_dupe."=".$dupe['dupe_id']."&editor=potlatch2";
		
		$duplicates[$i]=
			"Duplikat\n"
			.$link."\n"
			.$table
			."\n\n";
		
		if($i==$subscribers*10) break;
	}
	
	while($mail=mysql_fetch_assoc($mails)) {
		mail($mail['mail'], "Ein korrigierter Fehler am Tag", "Wie wär's heute mit diesem kleinen Fehler:\n\n".$problems[$i]."Und wenn noch etwas Zeit ist:\n".$duplicates[$i]."--\nBei Problemen oder Fragen oder wenn Sie sich abmelden wollen, besuchen Sie bitte http://gulp21.bplaced.net/osm/housenumbervalidator und nutzen Sie die angegebenen Kontaktmöglichkeiten oder den Link \"Ein korrigierter Fehler am Tag\" zum Abmelden.", "Content-Type: text/plain; charset=\"utf-8\"\nFrom: housenumbervalidator <support.gulp21@googlemail.com>");
		
		$problems[$i]=explode("\n",$problems[$i]);
		$duplicates[$i]=explode("\n",$duplicates[$i]);
		
		echo "<br/>".$mail['mail']."<br/>".$problems[$i][1]."<br/>".$duplicates[$i][1]."<br/>";
		
		$i-=10;
	}
	
?>
