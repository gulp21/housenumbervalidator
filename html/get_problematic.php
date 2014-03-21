<?php
	//! returns a list of problems in the db within @param bbox of @param prob_type [-1: all, 0: easy, 1: complicated] (max. 800)
	//! @param format
	//!     areastat: create a list of users which created the dupes in the bbox
	//!     simplelist: create a simple list which only contains street, house number, postcode, and city
	//!     gpx: export in GPS Exchange Format
	//!     default: csv (\t)
	
	include_once("functions.php");
	
	include_once("connect.php");
	
	mysql_set_charset("utf8");
	
	if($_GET['bbox']) {
		$bbox=explode(",",$_GET['bbox']);
		settype($bbox[0], "float");
		settype($bbox[1], "float");
		settype($bbox[2], "float");
		settype($bbox[3], "float");
	} else {
		$bbox[0]=0;
		$bbox[1]=0;
		$bbox[2]=50;
		$bbox[3]=50;
	}
	
	if(!is_null($_GET['prob_type'])) {
		$prob_type=$_GET['prob_type'];
	} else {
		$prob_type=-1;
	}
	
	if($_GET['format']=="gpx") {
		
		header("Content-Type: application/gpx+xml");
		
		echo '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>'."\n";
		
		$broks=mysql_query("SELECT * FROM problematic WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3]  AND corrected=0 AND (timestamp < NOW() - INTERVAL 5 DAY) LIMIT 800") or die ("MySQL-Error: ".mysql_error());
		
		echo '<gpx xmlns="http://www.topografix.com/GPX/1/1" creator="housenumbervalidator" version="1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">'."\n";
		
		while($brok=mysql_fetch_assoc($broks)) {
			
			if(($brok['easyfix']==1 && $prob_type==1) ||
			   ($brok['easyfix']==0 && $prob_type==0)) continue;
			
			echo "<wpt lon=\"$brok[lon]\" lat=\"$brok[lat]\">\n";
			
			$name = ($brok['easyfix']==1 ? "einfach" : "komplizierter");
			echo "<name><![CDATA[Problematisch, $name]]></name>\n";
			
			echo "<desc><![CDATA[";
			
			if($brok['broken'] & 1) echo "<b>country:</i> $brok[country]</b> ";
			else if($brok['country']!="") echo "<i>country:</i> $brok[country] ";
			
			if($brok['broken'] & 2) echo "<b><i>city:</i> $brok[city]</b> ";
			else if($brok['city']!="") echo "<i>city:</i> $brok[city] ";
			
			if($brok['broken'] & 4) echo "<b><i>postcode:</i> $brok[postcode]</b> ";
			else if($brok['postcode']!="") echo "<i>postcode:</i> $brok[postcode] ";
			
			if($brok['broken'] & 8) echo "<b><i>street:</i> $brok[street]</b> ";
			else if($brok['street']!="") echo "<i>street:</i> $brok[street] ";
			
			if($brok['broken'] & 16) echo "<b><i>housenumber:</i> $brok[number]</b> ";
			else if($brok['number']!="") echo "<i>housenumber:</i> $brok[number] ";
			
			if($brok['broken'] & 32) echo "<b><i>housename:</i> $brok[housename]</b> ";
			else if($brok['housename']!="") echo "<i>housename:</i> $brok[housename] ";
			
			echo "]]></desc>\n";
			
			echo "<extensions>\n";
			$errtype = ($brok[easyfix]==1 ? 0 : 1);
			echo "<error_type>2$errtype</error_type>\n";
			$type = ($brok['type']==1 ? "way" : "node");
			echo "<object_type>$type</object_type>\n";
			echo "<object_id>$brok[id]</object_id>\n";
			echo "</extensions>\n";
			
			echo "</wpt>\n";
		} // while mysql_fetch_assoc
		
		echo "</gpx>\n";
		
	} else if($_GET['format']=="simplelist") {
		
		header("Content-Type: text/plain; charset=UTF-8");
		
		$broks=mysql_query("SELECT * FROM (SELECT street,number,postcode,city FROM problematic WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 LIMIT 800) AS tmp ORDER BY city,postcode,street,number") or die ("MySQL-Error: ".mysql_error());
		
		echo "street number, postcode city\n";
		
		while($brok=mysql_fetch_assoc($broks)) {
			$street_number=$brok['street']." ".$brok['number'];
			$postcode_city=$brok['postcode']." ".$brok['city'];
			if(strlen($postcode_city)>1) {
				echo $street_number.", ".$postcode_city."\n";
			} else {
				echo $street_number."\n";
			}
		}
		
	} else if($_GET['format']=="areastat") {
		$broks=mysql_query("SELECT count(*) AS count, GROUP_CONCAT(type,'-',id) id, uid FROM `problematic` WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 GROUP BY uid ORDER BY count DESC LIMIT 800") or die ("MySQL-Error: ".mysql_error());
		
		echo '<style type="text/css">tr:nth-child(even){background-color:#f2f2f2;}</style>';
		echo "Diese Benutzer haben die meisten Probleme im angezeigten Bereich &quot;verursacht&quot;:<br/>";
		echo "(Tabelle wird beim Verschieben NICHT automatisch aktualisiert. Zum Aktualisieren zweimal auf &quot;Bereichsstatistik&quot; klicken.)<br/>";
		echo "<table>\n";
		echo "<tr style=\"font-weight:bold\"><td>#</td><td>Benutzer</td><td>Objekte</td></tr>";
		
		while($brok=mysql_fetch_assoc($broks)) {
			echo "<tr><td>".$brok['count']."</td><td>".generateUserLink($brok['uid'])."</td><td>".generateObjectLinks($brok['id'])."</td></tr>\n";
		}
		
		echo "</table>\n";
		
	} else {
		
		header("Content-Type: text/csv; charset=UTF-8");
		
		$broks=mysql_query("SELECT * FROM problematic WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3]  AND corrected=0 AND (timestamp < NOW() - INTERVAL 5 DAY) LIMIT 800") or die ("MySQL-Error: ".mysql_error());
		
		echo "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
		
		while($brok=mysql_fetch_assoc($broks)) {
			
			$iconSize="16";
			$clustertext="";
			
			$table='<table>';
			
			if(trim($brok['name'])!="") $table.="<tr><td>Name</td><td>".$brok['name']."</td></tr>";
			
			if($brok['broken'] & 1) $style=' class="broken" '; else $style='';
			if($brok['country']!="") $table.="<tr$style><td>addr:country</td><td>".$brok['country']."</td></tr>";
			
			if($brok['broken'] & 2) $style=' class="broken" '; else $style='';
			if($brok['city']!="") $table.="<tr$style><td>addr:city</td><td>".$brok['city']."</td></tr>";
			
			if($brok['broken'] & 4) $style=' class="broken" '; else $style='';
			if($brok['postcode']!="") $table.="<tr$style><td>addr:postcode</td><td>".$brok['postcode']."</td></tr>";
			
			if($brok['broken'] & 8) $style=' class="broken" '; else $style='';
			if($brok['street']!="") $table.="<tr$style><td>addr:street</td><td>".$brok['street']."</td></tr>";
			
			if($brok['broken'] & 16) $style=' class="broken" '; else $style='';
			if($brok['number']!="") $table.="<tr$style><td>addr:housenumber</td><td>".trim($brok['number'])."</td></tr>";
			
			if($brok['broken'] & 32) $style=' class="broken" '; else $style='';
			if($brok['housename']!="") $table.="<tr$style><td>addr:housename</td><td>".trim($brok['housename'])."</td></tr>";
			
			$table.="</table>";
			
			if($brok['type']==1) {
				$type="way";
			} else {
				$type="node";
			}
			
			if(($prob_type==-1 || $prob_type==0) && $brok['easyfix']==1) {
				$pin="pin_circle_red.png";
				$layer="prob_easy";
			} else if(($prob_type==-1 || $prob_type==1) && $brok['easyfix']==0) {
				$pin="pin_circle_blue.png";
				$layer="prob_complicated";
			} else {
				$pin="NULL";
			}
			
			// NOTE that most of them are not shown on the map, because we do not display problematic data younger than 5 days (Wall·E)
			if($brok['cluster']) {
				$pin=str_replace(".", "_cluster.", $pin);
				$iconSize="20";
				$clustertext="<small>(neuer Häufungspunkt)</small>";
			}
			
			$link=generateObjectLinkForBubble($brok['id'], $type, $brok['lat'], $brok['lon']);
			$corrected_link='<a target="josmframe" href="report.php?id='.$brok['id'].'&type='.$brok['type'].'&table=problematic" title="diesen Fehler als behoben markieren" onclick="javascript:markAsCorrectedClicked(\''.$brok['id'].'\', '.$brok['type'].', \''.$layer.'\');">&#10004;</a>';
			
			if($pin!="NULL") { // i.e. dupe is of requested dupe_type
				echo
					"$brok[lat]\t"
					."$brok[lon]\t"
					."Problematisch $clustertext $corrected_link\t"
					."<div>$link<br/>$table\t"
					."$pin\t"
					.$iconSize.",".$iconSize."\t"
					."-8,-8\n";
			}
		} // while mysql_fetch_assoc
		
	}
?>
