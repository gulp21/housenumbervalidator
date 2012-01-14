<html>
<body>
<?
if($_GET["way"]=="true") {
	$str="\n\t".trim($_GET["id"])."\t1";
} else {
	$str="\n\t".trim($_GET["id"])."\t0";
}
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
	print("Bitte die ID aus dem Popup in das Textfeld eingeben.");
	exit;
}
fputs($out,$str);
print("Vielen Dank! Nach der n&auml;chsten Aktualisierung wird dieser Fehler nicht mehr angezeigt.");
fclose($out);
?>
</body>
</html>