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
	
	$dupes=mysql_query("SELECT * FROM dupes WHERE lon BETWEEN $bbox[0] AND $bbox[2] AND lat BETWEEN $bbox[1] AND $bbox[3] AND corrected=0 LIMIT 800") or die ("MySQL-Error: ".mysql_error());
	
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
		
		if($dupe['type']==1) {
			$type="way";
			$t="w";
			$pin="pin_blue.png";
		} else {
			$type="node";
			$t="n";
			$pin="pin_red.png";
		}
		
		if($dupe['dupe_type']==1) {
			$type_dupe="way";
			$t_d="w";
		} else {
			$type_dupe="node";
			$t_d="n";
		}
		
		$link='<a target="_blank" title="Details bei OSM anzeigen" href="http://www.openstreetmap.org/browse/'.$type.'/'.$dupe['id'].'">'.$dupe['id'].'</a> <a target="josmframe" title="in JOSM bearbeiten" href="http://localhost:8111/load_object?objects='.$t.$dupe['id'].'&select='.$type.$dupe['id'].'"><img src="josm.png" alt="JOSM"/></a>&thinsp;<a target="_blank" title="in Potlatch 2 bearbeiten" href="http://www.openstreetmap.org/edit?zoom=18&'.$type.'='.$dupe['id'].'&editor=potlatch2"><img src="potlatch.png" alt="Potlatch"/></a>';
		
		$dupe_link='<a target="_blank" title="Details bei OSM anzeigen" href="http://www.openstreetmap.org/browse/'.$type_dupe.'/'.$dupe['dupe_id'].'">'.$dupe['dupe_id'].'</a> <a target="josmframe" title="in JOSM bearbeiten" href="http://localhost:8111/load_object?objects='.$t_d.$dupe['dupe_id'].'&select='.$type_dupe.$dupe['dupe_id'].'"><img src="josm.png" alt="JOSM"/></a>&thinsp;<a target="_blank" title="in Potlatch 2 bearbeiten" href="http://www.openstreetmap.org/edit?zoom=18&'.$type_dupe.'='.$dupe['dupe_id'].'&editor=potlatch2"><img src="potlatch.png" alt="Potlatch"/></a> [<a href="#" title="schwarzes Rechteck um Duplikat zeichnen" onclick="showPosition('.$dupe['dupe_lat'].','.$dupe['dupe_lon'].')">zeigen</a>]';
		
		$corrected_link='<a target="josmframe" href="report.php?id='.$dupe['id'].'&type='.$dupe['type'].'&table=dupes" title="diesen Fehler als behoben markieren" onclick="javascript:document.getElementsByName(\'id\')[0].value=\''.$dupe['id'].'\'; document.getElementsByName(\'way_u\')[0].checked='.$dupe['type'].'; document.getElementsByName(\'way\')[0].value=\''.$dupe['type'].'\';">&#10004;</a>';
		
		echo "$dupe[lat]\t"
			."$dupe[lon]\t"
			."Duplikat $corrected_link\t"
			."<div>$link ist Duplikat von<br/>$dupe_link<br/>$table</div>\t"
			."$pin\t"
			."16,16\t"
			."-8,-8\n";
	}
?>
