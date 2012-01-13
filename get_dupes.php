<?php
	mysql_connect("localhost", "gulp21_hnrv", "");
	mysql_select_db("gulp21_hnrv");
	
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
			."<tr><td>addr:number</td><td>".$dupe['number']."</td></tr>";
		
		if($dupe['type']===true) {
			$type="way";
			$pin="pin_blue.png";
		} else {
			$type="node";
			$pin="pin_red.png";
		}
		
		if($dupe['dupe_type']===true) {
			$type_dupe="way";
		} else {
			$type_dupe="node";
		}
		
		echo $dupes['lat'];
		echo $dupes['lat']+1;
		
		$link='<a target="_blank" href="http://www.openstreetmap.org/browse/'.$type.'/'.$dupe['id'].'">'.$dupe['id'].'</a> (<a target="josmframe" href="http://localhost:8111/load_and_zoom?left='/*.$dupes['lat']-0.000001.'&right='.$dupes['lat']+0.000001.'&top='.$dupes['lon']-0.000001.'&bottom='.$dupes['lon']+0.000001*/.'&select='.$type.$dupes['id'].'">JOSM</a>)';
		
		$dupe_link='<a target="_blank" href="http://www.openstreetmap.org/browse/'.$type_dupe.'/'.$dupe['dupe_id'].'">'.$dupe['dupe_id'].'</a> (<a target="josmframe" href="http://localhost:8111/load_and_zoom?left='.$dupes['dupe_lat']-0.000001.'&right='.$dupes['dupe_lat']+0.000001.'&top='.$dupes['dupe_lon']-0.000001.'&bottom='.$dupes['dupe_lon']+0.000001.'&select='.$type_dupe.$dupes['dupe_id'].'">JOSM</a>) [<a href="#" onclick="showPosition('.$dupes['dupe_lat'].','.$dupes['dupe_lon'].')">show</a>]';
		
		echo
			$dupe['lat']."\t"
			.$dupe['lon']."\t"
			."Dupe\t"
			.$link."<br/>".$table."<br/>is a dupe of".$dupe_link."\t"
			.$pin."\t"
			."16,16"."\t"
			."-8,-8\n";
	}
?>
