<?php
	include("connect.php");
	
	$dupes=mysql_query('SELECT * FROM dupes LIMIT 10');
	
	echo "lat\tlon\ttitle\tdescription\ticon\ticonSize\ticonOffset\n";
	
	while($dupe=mysql_fetch_assoc($dupes)) {
		
		$table="<table>";
		
		if(trim($dupe['name'])!="") $table.="<tr><td>Name</td><td>".$dupe['name']."</td></tr>";
		$table.=
			"<tr><td>addr:country</td><td>".$dupe['country']."</td></tr>"
			."<tr><td>addr:city</td><td>".$dupe['city']."</td></tr>"
			."<tr><td>addr:postcode</td><td>".$dupe['postcode']."</td></tr>"
			."<tr><td>addr:street</td><td>".$dupe['street']."</td></tr>"
			."<tr><td>addr:number</td><td>".$dupe['number']."</td></tr>"
			."</table>";
		
		if($dupe['type']==1) {
			$type="way";
			$pin="pin_blue.png";
		} else {
			$type="node";
			$pin="pin_red.png";
		}
		
		if($dupe['dupe_type']==1) {
			$type_dupe="way";
		} else {
			$type_dupe="node";
		}
		
		$link='<a target="_blank" href="http://www.openstreetmap.org/browse/'.$type.'/'.$dupe['id'].'">'.$dupe['id'].'</a> (<a target="josmframe" href="http://localhost:8111/load_and_zoom?left='.($dupe['lat']-0.000001).'&right='.($dupe['lat']+0.000001).'&top='.($dupe['lon']-0.000001).'&bottom='.($dupe['lon']+0.000001).'&select='.$type.$dupe['id'].'">JOSM</a>)';
		
		$dupe_link='<a target="_blank" href="http://www.openstreetmap.org/browse/'.$type_dupe.'/'.$dupe['dupe_id'].'">'.$dupe['dupe_id'].'</a> (<a target="josmframe" href="http://localhost:8111/load_and_zoom?left='.($dupe['dupe_lat']-0.000001).'&right='.($dupe['dupe_lat']+0.000001).'&top='.($dupe['dupe_lon']-0.000001).'&bottom='.($dupe['dupe_lon']+0.000001).'&select='.$type_dupe.$dupe['dupe_id'].'">JOSM</a>) [<a href="#" onclick="showPosition('.$dupe['dupe_lat'].','.$dupe['dupe_lon'].')">show</a>]';
		
		echo
			$dupe['lat']."\t"
			.$dupe['lon']."\t"
			."Dupe\t"
			."<div>".$link." is dupe of<br/>".$dupe_link."<br/>".$table."</div>\t"
			.$pin."\t"
			."16,16"."\t"
			."-8,-8\n";
	}
?>
