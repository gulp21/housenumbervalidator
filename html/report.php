<html>
<body>
<?
if($_GET["table"]=="dupes" || $_GET["table"]=="problematic") { // reports for corrected addresses
	include("connect.php");
	$id=mysql_real_escape_string($_GET["id"]);
	$type=mysql_real_escape_string($_GET["type"]);
	$query="UPDATE $_GET[table] SET corrected=1 WHERE id=$id AND type=$type";
	mysql_query($query) or print("<script language=\"javascript\">alert(\"MySQL-Error: ".mysql_error()." - $query\")</script>");
	$affected_rows=mysql_affected_rows();
	if($affected_rows==1) {
		print("<script language=\"javascript\">alert(\"$id wird vorerst nicht mehr angezeigt.\")</script>");
	} else {
		print("<script language=\"javascript\">alert(\"Irgendwas ist schief gelafen. ($affected_rows - $query)\")</script>");
	}
} else { // reports for false alarms
	$str="\n\t".trim($_GET["id"])."\t".$_GET["way"];
	$file = file_get_contents("reports.txt");
	if(strpos($file, $str)) {
		print("Vielen Dank! Nach der n&auml;chsten Aktualisierung wird dieser Fehler nicht mehr angezeigt. (Der Fehlalarm wurde bereits gemeldet.)");
		exit;
	}
	$out = fopen("reports.txt", "a");
	if(!$out) {
		print("Err");
		exit;
	}
	if(!is_numeric(trim($_GET["id"]))) {
		print("Die ID ist komisch&ellip;");
		exit;
	}
	fputs($out,$str);
	print("Vielen Dank! Nach der n&auml;chsten Aktualisierung wird dieser Fehler nicht mehr angezeigt.");
	fclose($out);
}
?>
</body>
</html>