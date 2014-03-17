<?php
	include("../connect.php");
	
	mysql_set_charset("utf8");
	
	$corrected=mysql_query("SELECT id, type FROM dupes WHERE corrected=1") or die ("MySQL-Error: ".mysql_error());
	$k=0;
	$corrected_dupes[0]=" 0 0";
	while($c=mysql_fetch_assoc($corrected)) {
		$corrected_dupes[$k]=" $c[id] $c[type]";
		++$k;
	}
	$oldClusters=mysql_query("SELECT id, type FROM dupes WHERE cluster=true AND (timestamp >= NOW() - INTERVAL 7 DAY)");
	$k=0;
	$oldClusters_dupes[0]=" 0 0";
	while($c=mysql_fetch_assoc($oldClusters)) {
		$oldClusters_dupes[$k]=" $c[id] $c[type]";
		++$k;
	}
	echo mysql_num_rows($corrected)." korrigiert, behalte ".mysql_num_rows($oldClusters)." alte Cluster<br/>";
	$k=0;
	
	$i=0;
	$file=file("/users/gulp21/www/osm/housenumbervalidator/update/dupes.txt") or die("ABORTED1");
	$fileclusters=file("/users/gulp21/www/osm/housenumbervalidator/update/dupeClusters.txt") or die("ABORTED2");
	mysql_query("DELETE FROM dupes") or die ("MySQL-Error: ".mysql_error());
	foreach($file as $line) {
		if(isset($line)) {
			$columns=explode("\t", $line);
			for($j=0;$j<15;$j++) $columns[$j]=mysql_real_escape_string($columns[$j]);
			if(!in_array(" $columns[2] $columns[3]", $corrected_dupes)) {
				$cluster = (in_array(" $columns[2] $columns[3]", $oldClusters_dupes) ? "true" : "false");
				mysql_query("INSERT INTO dupes VALUES ($columns[0], $columns[1], $columns[2], $columns[3],
				'$columns[4]', '$columns[5]', '$columns[6]', '$columns[7]', '$columns[8]', '$columns[9]',
				'$columns[10]', '$columns[11]', '$columns[12]', '$columns[13]', '$columns[14]', '$columns[15]',
				'$columns[16]', '$columns[17]', '".trim($columns[18])."',0,$cluster)")
				or die(mysql_error());
			} else {
				++$k;
			}
			++$i;
		}
	}
	$j=0;
	foreach($fileclusters as $line) {
		if(isset($line)) {
			$line=mysql_real_escape_string(explode("\t", $line)[2]);
			mysql_query("UPDATE dupes SET cluster=true WHERE id=$line") or die(mysql_error());
			++$j;
		}
	}
	echo "read $i lines from dupes.txt, $k corrected, $j new error clusters<br/>";
	if($i>1000) {
		unlink("/users/gulp21/www/osm/housenumbervalidator/update/dupes.txt");
		unlink("/users/gulp21/www/osm/housenumbervalidator/update/dupeClusters.txt");
		echo "removed dupes.txt, dupeClusters.txt<br/>";
	} else {
		echo "did NOT remove dupes.txt";
	}
	
	$corrected=mysql_query("SELECT id, type FROM problematic WHERE corrected=1") or die ("MySQL-Error: ".mysql_error());
	$k=0;
	$corrected_probs[0]=" 0 0";
	while($c=mysql_fetch_assoc($corrected)) {
		$corrected_probs[$k]=" $c[id] $c[type]";
		++$k;
	}
	$oldClusters=mysql_query("SELECT id, type FROM dupes WHERE cluster=true AND (timestamp >= NOW() - INTERVAL 7 DAY)");
	$k=0;
	$oldClusters_probs[0]=" 0 0";
	while($c=mysql_fetch_assoc($oldClusters)) {
		$oldClusters_probs[$k]=" $c[id] $c[type]";
		++$k;
	}
	echo mysql_num_rows($corrected)." korrigiert, behalte ".mysql_num_rows($oldClusters)." alte Cluster<br/>";
	$k=0;
	
	$i=0;
	$file=file("/users/gulp21/www/osm/housenumbervalidator/update/broken.txt") or die("ABORTED3");
	$fileclusters=file("/users/gulp21/www/osm/housenumbervalidator/update/brokenClusters.txt") or die("ABORTED4");
	mysql_query("DELETE FROM `problematic`") or die ("MySQL-Error: ".mysql_error());
	foreach($file as $line) {
		if(isset($line)) {
			$columns=explode("\t", $line);
			for($j=0;$j<11;$j++) $columns[$j]=mysql_real_escape_string($columns[$j]);
			if(!in_array(" $columns[2] $columns[3]", $corrected_probs)) {
				$cluster = (in_array(" $columns[2] $columns[3]", $oldClusters_probs) ? "true" : "false");
				mysql_query("INSERT INTO `problematic` VALUES ($columns[0], $columns[1], $columns[2], $columns[3], $columns[4], '$columns[5]', '$columns[6]', '$columns[7]', '$columns[8]', '$columns[9]', '$columns[10]', '$columns[11]', '$columns[12]', '$columns[13]', '".trim($columns[14])."', 0, $cluster)")
				or die(mysql_error());
			} else {
				++$k;
			}
			++$i;
		}
	}
	$j=0;
	foreach($fileclusters as $line) {
		if(isset($line)) {
			$line=mysql_real_escape_string(explode("\t", $line)[2]);
			mysql_query("UPDATE problematic SET cluster=true WHERE id=$line") or die(mysql_error());
			++$j;
		}
	}
	echo "read $i lines from broken.txt, $k corrected, $j new error clusters<br/>";
	if($i>10) {
		unlink("/users/gulp21/www/osm/housenumbervalidator/update/broken.txt");
		unlink("/users/gulp21/www/osm/housenumbervalidator/update/brokenClusters.txt");
		echo "removed broken.txt, brokenClusters.txt<br/>";
	} else {
		echo "did NOT remove broken.txt";
	}
	
	$i=0;
	$date="";
	$housenumbers=0;
	$dupes=0;
	$broken=0;
	$file=file("/users/gulp21/www/osm/housenumbervalidator/update/stats.txt") or die("ABORTED");
	foreach ($file as $line) {
		$l=explode("\t",$line);
		if($i==0) {
			$l=explode(".",$l[3]);
			$date=sprintf("%04d-%02d-%02d", $l[2], $l[1], $l[0]); 
		} else if($i==2) {
			$housenumbers=$l[0];
			$dupes=$l[2];
			$broken=$l[6];
			$i=-1;
			$c="insert into `stats` values ('$date', $housenumbers, $dupes, $broken, '0')";
			echo "$c<br/>";
			mysql_query($c) or die(mysql_error());
		}
		$i++;
	}
	unlink("/users/gulp21/www/osm/housenumbervalidator/update/stats.txt");
	echo "removed stats.txt<br/>";
	
	include("mail.php");
?>
