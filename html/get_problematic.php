<?php
	include("connect.php");
	
	header("Content-Type: text/csv; charset=UTF-8");
	
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
			$t="w";
		} else {
			$type="node";
			$t="n";
		}
		
		if(8==$brok['broken']) {
			$pin="pin_circle_red.png";
		} else {
			$pin="pin_circle_blue.png";
		}
		
		$link='<a target="_blank" title="Details bei OSM anzeigen" href="http://www.openstreetmap.org/browse/'.$type.'/'.$brok['id'].'">'.$brok['id'].'</a> <a target="josmframe" title="in JOSM bearbeiten" href="http://localhost:8111/load_object?objects='.$t.$brok['id'].'&select='.$type.$brok['id'].'"><img src="josm.png" alt="JOSM"/></a>&thinsp;<a target="_blank" title="in Potlatch 2 bearbeiten" href="http://www.openstreetmap.org/edit?zoom=18&'.$type.'='.$brok['id'].'&editor=potlatch2"><img src="potlatch.png" alt="Potlatch"/></a>';
		
		$corrected_link='<a target="josmframe" href="report.php?id='.$brok['id'].'&type='.$brok['type'].'&table=problematic" title="diesen Fehler als behoben markieren" onclick="javascript:document.getElementsByName(\'id\')[0].value=\''.$brok['id'].'\'; document.getElementsByName(\'way_u\')[0].checked='.$brok['type'].'; document.getElementsByName(\'way\')[0].value=\''.$brok['type'].'\';">&#10004;</a>';
		
		echo
			"$brok[lat]\t"
			."$brok[lon]\t"
			."Problematisch $corrected_link\t"
			."<div>$link<br/>$table\t"
			."$pin\t"
			."16,16\t"
			."-8,-8\n";
	}
?>
