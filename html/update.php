<?php /* TODO write a stat.txt file + read it; counter->db; removeme.php */
	include("../connect.php");
	
	mysql_set_charset("utf8");
	
	$i=0;
	$file=file("/users/gulp21/www/osm/housenumbervalidator/update/dupes.txt") or die("ABORTED");
	mysql_query("DELETE FROM dupes") or die ("MySQL-Error: ".mysql_error());
	foreach($file as $line) {
		if(isset($line)) {
			$columns=explode("\t", $line);
			mysql_query("insert into dupes values ($columns[0], $columns[1], $columns[2], $columns[3], '$columns[4]', '$columns[5]', '$columns[6]', '$columns[7]', '$columns[8]', '$columns[9]', '$columns[10]', '$columns[11]', '$columns[12]', '".trim($columns[13])."')")
			or die(mysql_error());
			$i++;
		}
	}
	echo "read ".$i." lines from dupes.txt<br/>";
	if($i>1000) {
		unlink("/users/gulp21/www/osm/housenumbervalidator/update/dupes.txt");
		echo "removed dupes.txt<br/>";
	}
	
	$i=0;
	$file=file("/users/gulp21/www/osm/housenumbervalidator/update/broken.txt") or die("ABORTED");
	mysql_query("DELETE FROM `problematic`") or die ("MySQL-Error: ".mysql_error());
	foreach($file as $line) {
		if(isset($line)) {
			$columns=explode("\t", $line);
			mysql_query("insert into `problematic` values ($columns[0], $columns[1], $columns[2], $columns[3], $columns[4], '$columns[5]', '$columns[6]', '$columns[7]', '$columns[8]', '$columns[9]', '".trim($columns[10])."')")
			or die(mysql_error());
			$i++;
		}
	}
	echo "read ".$i." lines from broken.txt<br/>";
	if($i>1000) {
		unlink("/users/gulp21/www/osm/housenumbervalidator/update/broken.txt");
		echo "removed broken.txt<br/>";
	}
?>
