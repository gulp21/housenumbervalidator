<?php
	//! returns a list of dupes in the db within @param bbox of @param dupe_type [-1: all, 0: near, 1: exact, 2: similar] (max. 800)
	//! @param areastat=1: create a list of users which created the dupes in the bbox
	//! @param simplelist=1: create a simple list which only contains street, house number, postcode, and city
	
	include_once("functions.php");
	
	include_once("connect.php");
	
	mysql_set_charset("utf8");
	
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
	
	if($_GET['simplelist']==1) {
		
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
		
	} else if($_GET['areastat']!=1) {
		
		header("Content-Type: text/csv; charset=UTF-8");
		
		$dupes=mysql_query("SELECT *,(lat-dupe_lat) AS d_lat,(lon-dupe_lon) AS d_lon FROM dupes WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 LIMIT 800") or die ("MySQL-Error: ".mysql_error());
		
		echo "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
		
		while($dupe=mysql_fetch_assoc($dupes)) {
			
			$table="<table>";
			
			if(trim($dupe['name'])!="") $table.="<tr><td>Name</td><td>".$dupe['name']."</td></tr>";
			if($dupe['country']!="") $table.="<tr><td>addr:country</td><td>".$dupe['country']."</td></tr>";
			if($dupe['city']!="") $table.="<tr><td>addr:city</td><td>".$dupe['city']."</td></tr>";
			if($dupe['postcode']!="") $table.="<tr><td>addr:postcode</td><td>".$dupe['postcode']."</td></tr>";
			if($dupe['street']!="") $table.="<tr><td>addr:street</td><td>".$dupe['street']."</td></tr>";
			if($dupe['number']!="") $table.="<tr><td>addr:housenumber</td><td>".$dupe['number']."</td></tr>";
			if($dupe['housename']!="") $table.="<tr><td>addr:housename</td><td>".$dupe['housename']."</td></tr>";
			$table.="</table>";
			
			$VERY_NEAR_THRESHOLD=0.00002;
			
			if ($dupe['d_lat']<$VERY_NEAR_THRESHOLD && $dupe['d_lon']<$VERY_NEAR_THRESHOLD
			&& $dupe['d_lat']>-$VERY_NEAR_THRESHOLD && $dupe['d_lon']>-$VERY_NEAR_THRESHOLD) {
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
			
			$link='<a target="_blank" title="Details bei OSM anzeigen" href="http://www.openstreetmap.org/browse/'.$type.'/'.$dupe['id'].'">'.$dupe['id'].'</a> <a target="josmframe" title="in JOSM bearbeiten" href="http://localhost:8111/load_and_zoom?left='.($dupe['lon']-0.001).'&right='.($dupe['lon']+0.001).'&top='.($dupe['lat']+0.001).'&bottom='.($dupe['lat']-0.001).'&select='.$type.$dupe['id'].'"><img src="josm.png" alt="JOSM"/></a>&thinsp;<a target="_blank" title="in Potlatch 2 bearbeiten" href="http://www.openstreetmap.org/edit?zoom=18&'.$type.'='.$dupe['id'].'&editor=potlatch2"><img src="potlatch.png" alt="Potlatch"/></a>';
			
			$dupe_link='<a target="_blank" title="Details bei OSM anzeigen" href="http://www.openstreetmap.org/browse/'.$type_dupe.'/'.$dupe['dupe_id'].'">'.$dupe['dupe_id'].'</a> <a target="josmframe" title="in JOSM bearbeiten" href="http://localhost:8111/load_and_zoom?left='.($dupe['dupe_lon']-0.001).'&right='.($dupe['dupe_lon']+0.001).'&top='.($dupe['dupe_lat']+0.001).'&bottom='.($dupe['dupe_lat']-0.001).'&select='.$type_dupe.$dupe['dupe_id'].'"><img src="josm.png" alt="JOSM"/></a>&thinsp;<a target="_blank" title="in Potlatch 2 bearbeiten" href="http://www.openstreetmap.org/edit?zoom=18&'.$type_dupe.'='.$dupe['dupe_id'].'&editor=potlatch2"><img src="potlatch.png" alt="Potlatch"/></a> [<a href="#" title="schwarzes Rechteck um Duplikat zeichnen" onclick="showPosition('.$dupe['dupe_lat'].','.$dupe['dupe_lon'].')">zeigen</a>]';
			
			$corrected_link='<a target="josmframe" href="report.php?id='.$dupe['id'].'&type='.$dupe['type'].'&table=dupes" title="diesen Fehler als behoben markieren" onclick="javascript:markAsCorrectedClicked(\''.$dupe['id'].'\', '.$dupe['type'].', \''.$layer.'\');">&#10004;</a>';
			
			if($pin!="NULL") { // i.e. dupe is of requested dupe_type
				echo "$dupe[lat]\t"
					."$dupe[lon]\t"
					."Duplikat $corrected_link\t"
					."<div>$link ist Duplikat von<br/>$dupe_link<br/>$table</div>\t"
					."$pin\t"
					."16,16\t"
					."-8,-8\n";
			}
		} // while mysql_fetch_assoc
		
	} else {
		
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
		
	}
?>
