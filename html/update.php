<?php /* TODO counter->db; removeme.php */
	include("../connect.php");
	
	mysql_set_charset("utf8");
	
	$i=0;
	$file=file("/users/gulp21/www/osm/housenumbervalidator/update/dupes.txt") or die("ABORTED");
	
	$corrected=mysql_query("SELECT id, type FROM dupes WHERE corrected=1") or die ("MySQL-Error: ".mysql_error());
	echo mysql_num_rows($corrected)." korrigiert<br/>";
	$k=0;
	$corrected_dupes[0]=" 0 0";
	while($c=mysql_fetch_assoc($corrected)) {
		$corrected_dupes[$k]=" $c[id] $c[type]";
		++$k;
	}
	$k=0;
	
	mysql_query("DELETE FROM dupes") or die ("MySQL-Error: ".mysql_error());
	foreach($file as $line) {
		if(isset($line)) {
			$columns=explode("\t", $line);
			for($j=0;$j<15;$j++) $columns[$j]=mysql_real_escape_string($columns[$j]);
			if(!in_array(" $columns[2] $columns[3]", $corrected_dupes)) {
				mysql_query("insert into dupes values ($columns[0], $columns[1], $columns[2], $columns[3],
				'$columns[4]', '$columns[5]', '$columns[6]', '$columns[7]', '$columns[8]', '$columns[9]',
				'$columns[10]', '$columns[11]', '$columns[12]', '$columns[13]', '".trim($columns[14])."','0')")
				or die(mysql_error());
			} else {
				++$k;
			}
			++$i;
		}
	}
	echo "read $i lines from dupes.txt, corrected $k<br/>";
	if($i>1000) {
		unlink("/users/gulp21/www/osm/housenumbervalidator/update/dupes.txt");
		echo "removed dupes.txt<br/>";
	}
	
	$corrected=mysql_query("SELECT id, type FROM problematic WHERE corrected=1") or die ("MySQL-Error: ".mysql_error());
	echo mysql_num_rows($corrected)." korrigiert<br/>";
	$k=0;
	$corrected_probs[0]=" 0 0";
	while($c=mysql_fetch_assoc($corrected)) {
		$corrected_probs[$k]=" $c[id] $c[type]";
		++$k;
	}
	$k=0;
	
	$i=0;
	$file=file("/users/gulp21/www/osm/housenumbervalidator/update/broken.txt") or die("ABORTED");
	mysql_query("DELETE FROM `problematic`") or die ("MySQL-Error: ".mysql_error());
	foreach($file as $line) {
		if(isset($line)) {
			$columns=explode("\t", $line);
			for($j=0;$j<11;$j++) $columns[$j]=mysql_real_escape_string($columns[$j]);
			
			if(!in_array(" $columns[2] $columns[3]", $corrected_probs)) {
				mysql_query("insert into `problematic` values ($columns[0], $columns[1], $columns[2], $columns[3], $columns[4], '$columns[5]', '$columns[6]', '$columns[7]', '$columns[8]', '$columns[9]', '$columns[10]', '".trim($columns[11])."', '0')")
			or die(mysql_error());
			} else {
				++$k;
			}
			++$i;
		}
	}
	echo "read ".$i." lines from broken.txt, corrected $k<br/>";
	if($i>100) {
		unlink("/users/gulp21/www/osm/housenumbervalidator/update/broken.txt");
		echo "removed broken.txt<br/>";
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
			$c="insert into `stats` values ('$date', $housenumbers, $dupes, $broken, 0)";
			echo "$c<br/>";
			mysql_query($c) or die(mysql_error());
		}
		$i++;
	}
	unlink("/users/gulp21/www/osm/housenumbervalidator/update/stats.txt");
	echo "removed stats.txt<br/>";
	
	include("mail.php");
?>
