<?php
	include("../connect.php");
	
	mysql_set_charset("utf8");
	
	$mails=mysql_query("SELECT * FROM mails") or die ("MySQL-Error: ".mysql_error());
	
	$subscribers=mysql_num_rows($mails);
	
	echo $subscribers." subscribers<br/>";
	
	$probs=mysql_query("SELECT * FROM problematic WHERE `broken` BETWEEN '1' AND '7' OR `street` REGEXP '.*str\.? ?[0-9].*' OR `street` REGEXP '\<.*'") or die ("MySQL-Error: ".mysql_error());
	
	echo "Found ".mysql_num_rows($probs)." Ps<br/>";
	
	$i=-rand(0,mysql_num_rows($probs)-$subscribers);
	
	echo "i ".$i."<br/>";
	
	while($prob=mysql_fetch_assoc($probs)) {
		
		if($i++<0) continue;
		
		$table="";
		
		if(trim($prob['name'])!="") $table.="Name\t".$prob['name']."\n";
		
		if(($prob['broken']|1)==$prob['broken']) $style='*'; else $style='';
		if($prob['country']!="") $table.=$style."addr:country\t".$prob['country'].$style."\n";
		
		if(($prob['broken']|2)==$prob['broken']) $style='*'; else $style='';
		if($prob['city']!="") $table.=$style."addr:city\t".$prob['city'].$style."\n";
		
		if(($prob['broken']|4)==$prob['broken']) $style='*'; else $style='';
		if($prob['postcode']!="") $table.=$style."addr:postcode\t".$prob['postcode'].$style."\n";
		
		if(($prob['broken']|8)==$prob['broken']) $style='*'; else $style='';
		if($prob['street']!="") $table.=$style."addr:street\t".$prob['street'].$style."\n";
		
		if(($prob['broken']|16)==$prob['broken']) $style='*'; else $style='';
		if($prob['number']!="") $table.=$style."addr:number\t".$prob['number'].$style."\n";
		
		if($prob['type']==1) {
			$type="way";
		} else {
			$type="node";
		}
		
		$link="OSM: http://www.openstreetmap.org/browse/".$type."/".$prob['id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($prob['lon']-0.0001)."&right=".($prob['lon']+0.0001)."&top=".($prob['lat']+0.0001)."&bottom=".($prob['lat']-0.0001)."&select=".$type.$prob['id'];
		
		$problems[$i]=
			"Problematisch\n"
			.$link."\n"
			.$table
			."\n";
		
		if($i==$subscribers) break;
	}
	
	$dupes=mysql_query("SELECT * FROM dupes") or die ("MySQL-Error: ".mysql_error());
	
	echo "Found ".mysql_num_rows($dupes)." Ds<br/>";
	
	$i=-rand(0,mysql_num_rows($dupes)-$subscribers);
	
	echo "i ".$i."<br/>";
	
	while($dupe=mysql_fetch_assoc($dupes)) {
		
		if($i++<0) continue;
		
		$table="";
		
		if(trim($dupe['name'])!="") $table.="Name\t".$dupe['name']."\n";
		
		if(($dupe['broken']|1)==$dupe['broken']) $style='*'; else $style='';
		if($dupe['country']!="") $table.=$style."addr:country\t".$dupe['country'].$style."\n";
		
		if(($dupe['broken']|2)==$dupe['broken']) $style='*'; else $style='';
		if($dupe['city']!="") $table.=$style."addr:city\t".$dupe['city'].$style."\n";
		
		if(($dupe['broken']|4)==$dupe['broken']) $style='*'; else $style='';
		if($dupe['postcode']!="") $table.=$style."addr:postcode\t".$dupe['postcode'].$style."\n";
		
		if(($dupe['broken']|8)==$dupe['broken']) $style='*'; else $style='';
		if($dupe['street']!="") $table.=$style."addr:street\t".$dupe['street'].$style."\n";
		
		if(($dupe['broken']|16)==$dupe['broken']) $style='*'; else $style='';
		if($dupe['number']!="") $table.=$style."addr:number\t".$dupe['number'].$style."\n";
		
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
		
		$link="OSM: http://www.openstreetmap.org/browse/".$type."/".$dupe['id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($dupe['lon']-0.0001)."&right=".($dupe['lon']+0.0001)."&top=".($dupe['lat']+0.0001)."&bottom=".($dupe['lat']-0.0001)."&select=".$type.$dupe['id'];
		
		$link.="\nOSM: http://www.openstreetmap.org/browse/".$type_dupe."/".$dupe['dupe_id']."\nJOSM: http://localhost:8111/load_and_zoom?left=".($dupe['dupe_lon']-0.0001)."&right=".($dupe['dupe_lon']+0.0001)."&top=".($dupe['dupe_lat']+0.0001)."&bottom=".($dupe['dupe_lat']-0.0001)."&select=".$type_dupe.$dupe['dupe_id'];
		
		$duplicates[$i]=
			"Duplikat\n"
			.$link."\n"
			.$table
			."\n\n";
		
		if($i==$subscribers) break;
	}
	
	while($mail=mysql_fetch_assoc($mails)) {
		mail($mail['mail'], "Ein korrigierter Fehler am Tag", "Wie wär's heute mit diesem kleinen Fehler:\n\n".$problems[$i]."Und wenn noch etwas Zeit ist:\n".$duplicates[$i]."--\nBei Problemen oder Fragen oder wenn Sie sich abmelden wollen, besuchen Sie bitte http://gulp21.bplaced.net/osm/housenumbervalidator und nutzen Sie die angegebenen Kontaktmöglichkeiten oder den Link \"Ein korrigierter Fehler am Tag\" zum Abmelden.", "Content-Type: text/plain; charset=\"utf-8\"\nFrom: housenumbervalidator <support.gulp21@googlemail.com>");
		
		$problems[$i]=explode("\n",$problems[$i]);
		$duplicates[$i]=explode("\n",$duplicates[$i]);
		
		echo "<br/>".$mail['mail']."<br/>".$problems[$i][1]."<br/>".$duplicates[$i][1]."<br/><br/>";
		
		$i--;
	}
	
?>
