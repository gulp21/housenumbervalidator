<?php
	//! returns a list of dupes in the db within @param bbox of @param dupe_type [-1: all, 0: near, 1: exact, 2: similar] (max. 800)
	//! @param format
	//!     areastat: create a list of users which created the dupes in the bbox
	//!     simplelist: create a simple list which only contains street, house number, postcode, and city
	//!     gpx: export in GPS Exchange Format
	//!     default: csv (\t)
	
	include_once("functions.php");
	
	include_once("connect.php");
	
	mysql_set_charset("utf8");
	
	$VERY_NEAR_THRESHOLD=0.00002;
	
	if(!is_null($_GET['bbox'])) {
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
	
	if(!is_null($_GET['dupe_type'])) {
		$dupe_type=$_GET['dupe_type'];
	} else {
		$dupe_type=-1;
	}
	
	if($_GET['format']=="gpx") {
		
		header("Content-Type: application/gpx+xml");
		
		echo '<?xml version="1.0" encoding="UTF-8" standalone="no" ?>'."\n";
		$dupes=mysql_query("SELECT *,(lat-dupe_lat) AS d_lat,(lon-dupe_lon) AS d_lon FROM dupes WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 LIMIT 800") or die ("MySQL-Error: ".mysql_error());
		
		echo '<gpx xmlns="http://www.topografix.com/GPX/1/1" creator="housenumbervalidator" version="1.1" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.topografix.com/GPX/1/1 http://www.topografix.com/GPX/1/1/gpx.xsd">'."\n";
		
		
		while($dupe=mysql_fetch_assoc($dupes)) {
			$name="NULL";
			$errtype=-1;
			if ($dupe['d_lat']<=$VERY_NEAR_THRESHOLD && $dupe['d_lon']<=$VERY_NEAR_THRESHOLD
			 && $dupe['d_lat']>=-$VERY_NEAR_THRESHOLD && $dupe['d_lon']>=-$VERY_NEAR_THRESHOLD) {
				if($dupe_type==-1 || $dupe_type==0) {
					$name="Sehr nah";
					$errtype=0;
				}
			} else if(($dupe_type==-1 || $dupe_type==1)
				  && $dupe['possible_dupe']==0) {
				$name="Exakt";
				$errtype=1;
			} else if(($dupe_type==-1 || $dupe_type==2)
				  && $dupe['possible_dupe']==1) {
				$name="Ähnlich";
				$errtype=2;
			}
			
			if($errtype==-1) continue;
			
			echo "<wpt lon=\"$dupe[lon]\" lat=\"$dupe[lat]\">\n";
			
			echo "<name><![CDATA[Duplikat, $name]]></name>\n";
			
			echo "<desc><![CDATA[";
			
			echo ($dupe['type']==1 ? "Weg" : "Knoten")." ".$dupe["id"]." ";
			echo "ist ein Duplikat von ";
			echo ($dupe['dupe_type']==1 ? "Weg" : "Knoten")." ".$dupe["dupe_id"].": ";
			
			if(trim($dupe['name'])!="") echo $dupe['name']." ";
			
			$street_number=$dupe['street']." ".$dupe['number'];
			$postcode_city=trim($dupe['postcode']." ".$dupe['city']);
			if(strlen($postcode_city)>1) {
				echo "<i>".$street_number.", ".$postcode_city."</i>";
			} else {
				echo "<i>".$street_number."</i>";
			}
			
			echo "]]></desc>\n";
			
			echo "<extensions>\n";
			echo "<error_type>1$errtype</error_type>\n";
			$type = ($dupe['type']==1 ? "way" : "node");
			echo "<object_type>$type</object_type>\n";
			echo "<object_id>$dupe[id]</object_id>\n";
			echo "</extensions>\n";
			
			echo "</wpt>\n";
		} // while mysql_fetch_assoc
		
		echo "</gpx>\n";
		
	} else if($_GET['format']=="simplelist") {
		
		header("Content-Type: text/plain; charset=UTF-8");
		
		$dupes=mysql_query("SELECT * FROM (SELECT street,number,postcode,city FROM dupes WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 LIMIT 800) AS tmp ORDER BY city,postcode,street,number") or die ("MySQL-Error: ".mysql_error());
		
		echo "street number, postcode city\n";
		
		while($dupe=mysql_fetch_assoc($dupes)) {
			$street_number=$dupe['street']." ".$dupe['number'];
			$postcode_city=trim($dupe['postcode']." ".$dupe['city']);
			if(strlen($postcode_city)>1) {
				echo $street_number.", ".$postcode_city."\n";
			} else {
				echo $street_number."\n";
			}
		}
		
	} else if($_GET['format']=="areastat") {
		
		$dupes=mysql_query("
			SELECT count(*) AS count, GROUP_CONCAT(type,'-',id) id, GROUP_CONCAT(dupe_type,'-',dupe_id) dupe_id, uid 
			FROM 
			(
				(SELECT id, type, uid, dupe_id, dupe_type, dupe_uid FROM `dupes`
				WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0)
				UNION
				(SELECT dupe_id, dupe_type, dupe_uid, id, type, uid FROM `dupes`
				WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0)
			) AS tmp
			GROUP BY uid
			ORDER BY count DESC
			LIMIT 100
			") or die ("MySQL-Error: ".mysql_error());
		
		echo '<style type="text/css">tr:nth-child(even){background-color:#f2f2f2;}</style>';
		echo "Diese Benutzer haben die meisten Duplikate im angezeigten Bereich &quot;verursacht&quot;:<br/>";
		echo "(Tabelle wird beim Verschieben NICHT automatisch aktualisiert. Zum Aktualisieren zweimal auf &quot;Bereichsstatistik&quot; klicken. Jedes Objekt taucht zweimal in der Liste auf.)<br/>";
		echo "<table>\n";
		echo "<tr style=\"font-weight:bold\"><td>#</td><td>Benutzer</td><td>Objekte</td><td>sind Duplikat von</td></tr>";
		
		while($dupe=mysql_fetch_assoc($dupes)) {
			echo "<tr><td>".$dupe['count']."</td><td>".generateUserLink($dupe['uid'])."</td><td>".generateObjectLinks($dupe['id'])."</td><td>".generateObjectLinks($dupe['dupe_id'])."</td></tr>\n";
		}
		
		echo "</table>\n";
		
	} else {
		
		header("Content-Type: text/csv; charset=UTF-8");
		
		$dupes=mysql_query("
		(SELECT *,(lat-dupe_lat) AS d_lat,(lon-dupe_lon) AS d_lon FROM dupes WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 LIMIT 800)
		UNION
		(SELECT *,(lat-dupe_lat) AS d_lat,(lon-dupe_lon) AS d_lon FROM dupes WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 AND cluster=true LIMIT 50)
		") or die ("MySQL-Error: ".mysql_error());
		
		echo "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
		
		while($dupe=mysql_fetch_assoc($dupes)) {
			
			$iconSize="16";
			$clustertext="";
			
			$table="<table>";
			
			if(trim($dupe['name'])!="") $table.="<tr><td>Name</td><td>".$dupe['name']."</td></tr>";
			if($dupe['country']!="") $table.="<tr><td>addr:country</td><td>".$dupe['country']."</td></tr>";
			if($dupe['city']!="") $table.="<tr><td>addr:city</td><td>".$dupe['city']."</td></tr>";
			if($dupe['postcode']!="") $table.="<tr><td>addr:postcode</td><td>".$dupe['postcode']."</td></tr>";
			if($dupe['street']!="") $table.="<tr><td>addr:street</td><td>".$dupe['street']."</td></tr>";
			if($dupe['number']!="") $table.="<tr><td>addr:housenumber</td><td>".$dupe['number']."</td></tr>";
			if($dupe['housename']!="") $table.="<tr><td>addr:housename</td><td>".$dupe['housename']."</td></tr>";
			$table.="</table>";
			
			if ($dupe['d_lat']<=$VERY_NEAR_THRESHOLD && $dupe['d_lon']<=$VERY_NEAR_THRESHOLD
			&& $dupe['d_lat']>=-$VERY_NEAR_THRESHOLD && $dupe['d_lon']>=-$VERY_NEAR_THRESHOLD) {
				if($dupe_type==-1 || $dupe_type==0) {
					$pin="pin_pink.png";
					$layer="dupes_near";
				} else {
					$pin="NULL";
				}
			} else if(($dupe_type==-1 || $dupe_type==1)
				&& $dupe['possible_dupe']==0) {
				$pin="pin_red.png";
				$layer="dupes_exact";
			} else if(($dupe_type==-1 || $dupe_type==2)
				&& $dupe['possible_dupe']==1) {
				$pin="pin_blue.png";
				$layer="dupes_similar";
			} else {
				$pin="NULL";
			}
			
			if($dupe['cluster']) {
				$pin=str_replace(".", "_cluster.", $pin);
				$iconSize="20";
				$clustertext="<small>(neuer Häufungspunkt)</small>";
			}
			
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
			
			$link=generateObjectLinkForBubble($dupe['id'], $type, $dupe['lat'], $dupe['lon']);
			
			$dupe_link=generateObjectLinkForBubble($dupe['dupe_id'], $type_dupe,$dupe['dupe_lat'], $dupe['dupe_lon'])
			          .'[<a href="#" title="schwarzes Rechteck um Duplikat zeichnen" onclick="showPosition('.$dupe['dupe_lat'].','.$dupe['dupe_lon'].')">zeigen</a>]';
			
			$corrected_link='<a target="josmframe" href="report.php?id='.$dupe['id'].'&type='.$dupe['type'].'&table=dupes" title="diesen Fehler als behoben markieren" onclick="javascript:markAsCorrectedClicked(\''.$dupe['id'].'\', '.$dupe['type'].', \''.$layer.'\');">&#10004;</a>';
			
			if($pin!="NULL") { // i.e. dupe is of requested dupe_type
				echo "$dupe[lat]\t"
					."$dupe[lon]\t"
					."Duplikat $clustertext $corrected_link\t"
					."<div>$link ist Duplikat von<br/>$dupe_link<br/>$table</div>\t"
					."$pin\t"
					.$iconSize.",".$iconSize."\t"
					."-8,-8\n";
			}
		} // while mysql_fetch_assoc
		
	}
?>
