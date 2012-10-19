<?php
	//! returns a list of problems in the db within @param bbox of @prob_type [-1: all, 0: easy, 1: complicated] (max. 800)
	//! @param areastat=1: create a list of users which created the problems in the bbox
	//! @param simplelist=1: create a simple list which only contains street, house number, postcode, and city
	
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
	
	if($_GET['simplelist']==1) {
		
		header("Content-Type: text/plain; charset=UTF-8");
		
		$broks=mysql_query("SELECT street,number,postcode,city FROM problematic WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 LIMIT 800") or die ("MySQL-Error: ".mysql_error());
		
		echo "street number, postcode city\n";
		
		while($brok=mysql_fetch_assoc($broks)) {
			$street_number=$brok['street']." ".$dupe['number'];
			$postcode_city=$brok['postcode']." ".$dupe['city'];
			if(strlen($postcode_city)>1) {
				echo $street_number.", ".$postcode_city."\n";
			} else {
				echo $street_number."\n";
			}
		}
		
	} else if($_GET['areastat']!=1) {
		
		header("Content-Type: text/csv; charset=UTF-8");
		
		$broks=mysql_query("SELECT * FROM problematic WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3]  AND corrected=0 LIMIT 800") or die ("MySQL-Error: ".mysql_error());
		
		echo "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
		
		while($brok=mysql_fetch_assoc($broks)) {
			
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
			
			$link='<a target="_blank" title="Details bei OSM anzeigen" href="http://www.openstreetmap.org/browse/'.$type.'/'.$brok['id'].'">'.$brok['id'].'</a> <a target="josmframe" title="in JOSM bearbeiten" href="http://localhost:8111/load_and_zoom?left='.($brok['lon']-0.001).'&right='.($brok['lon']+0.001).'&top='.($brok['lat']+0.001).'&bottom='.($brok['lat']-0.001).'&select='.$type.$brok['id'].'"><img src="josm.png" alt="JOSM"/></a>&thinsp;<a target="_blank" title="in Potlatch 2 bearbeiten" href="http://www.openstreetmap.org/edit?zoom=18&'.$type.'='.$brok['id'].'&editor=potlatch2"><img src="potlatch.png" alt="Potlatch"/></a>';
			
			$corrected_link='<a target="josmframe" href="report.php?id='.$brok['id'].'&type='.$brok['type'].'&table=problematic" title="diesen Fehler als behoben markieren" onclick="javascript:markAsCorrectedClicked(\''.$brok['id'].'\', '.$brok['type'].', \''.$layer.'\');">&#10004;</a>';
			
			if($pin!="NULL") { // i.e. dupe is of requested dupe_type
				echo
					"$brok[lat]\t"
					."$brok[lon]\t"
					."Problematisch $corrected_link\t"
					."<div>$link<br/>$table\t"
					."$pin\t"
					."16,16\t"
					."-8,-8\n";
			}
		} // while mysql_fetch_assoc
		
	} else {
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
		
	}
?>
