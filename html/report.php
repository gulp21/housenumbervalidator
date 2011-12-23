<html>
<body>
<?
$out = fopen("reports.txt", "a");
if (!$out) {
    print("Err");
    exit;
}
if($_GET["way"]==="true") {
	fputs($out,"way".$_GET["id"]."\"\n");
	print("Vielen Dank! Nach der n&auml;chsten Aktualisierung wird dieser Weg nicht mehr angezeigt.");
} else {
	fputs($out,"node".$_GET["id"]."\"\n");
	print("Vielen Dank! Nach der n&auml;chsten Aktualisierung wird dieser Knoten nicht mehr angezeigt.");
}
fclose($out);
?>
</body>
</html>