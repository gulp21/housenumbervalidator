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
	
	$broks=mysql_query("SELECT * FROM problematic WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] LIMIT 800") or die ("MySQL-Error: ".mysql_error());
	
	echo "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
	
	while($brok=mysql_fetch_assoc($broks)) {
		
		$table='<table>';
		
		if(trim($brok['name'])!="") $table.="<tr><td>Name</td><td>".$brok['name']."</td></tr>";
		
		if(($brok['broken']|1)==$brok['broken']) $style=' class="broken" '; else $style='';
		if($brok['country']!="") $table.="<tr$style><td>addr:country</td><td>".$brok['country']."</td></tr>";
		
		if(($brok['broken']|2)==$brok['broken']) $style=' class="broken" '; else $style='';
		if($brok['city']!="") $table.="<tr$style><td>addr:city</td><td>".$brok['city']."</td></tr>";
		
		if(($brok['broken']|4)==$brok['broken']) $style=' class="broken" '; else $style='';
		if($brok['postcode']!="") $table.="<tr$style><td>addr:postcode</td><td>".$brok['postcode']."</td></tr>";
		
		if(($brok['broken']|8)==$brok['broken']) $style=' class="broken" ' ;else $style='';
		if($brok['street']!="") $table.="<tr$style><td>addr:street</td><td>".$brok['street']."</td></tr>";
		
		if(($brok['broken']|16)==$brok['broken']) $style=' class="broken" '; else $style='';
		if($brok['number']!="") $table.="<tr$style><td>addr:housenumber</td><td>".$brok['number']."</td></tr>";
		
		$table.="</table>";
		
		if($brok['type']==1) {
			$type="way";
		} else {
			$type="node";
		}
		
		if(($brok['broken']|8)==$brok['broken']) {
			$pin="pin_circle_red.png";
		} else {
			$pin="pin_circle_blue.png";
		}
		
		$link='<a target="_blank" href="http://www.openstreetmap.org/browse/'.$type.'/'.$brok['id'].'">'.$brok['id'].'</a> (<a target="josmframe" href="http://localhost:8111/load_and_zoom?left='.($brok['lon']-0.0001).'&right='.($brok['lon']+0.0001).'&top='.($brok['lat']+0.0001).'&bottom='.($brok['lat']-0.0001).'&select='.$type.$brok['id'].'">JOSM</a>)';
		
		echo
			$brok['lat']."\t"
			.$brok['lon']."\t"
			."Problematic\t"
			."<div>".$link."<br/>".$table."\t"
			.$pin."\t"
			."16,16"."\t"
			."-8,-8\n";
	}
?>
