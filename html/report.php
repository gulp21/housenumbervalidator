<html>
<body>
<?
$out = fopen("reports.txt", "a");
if(!$out) {
    print("Err");
    exit;
}
if(!is_numeric($_GET["id"])) {
	print("Bitte die ID aus dem Popup in das Textfeld eingeben.");
	exit;
}
if($_GET["way"]==="true") {
	fputs($out,"\n"."way".$_GET["id"]."\"");
	print("Vielen Dank! Nach der n&auml;chsten Aktualisierung wird dieser Weg nicht mehr angezeigt.");
} else {
	fputs($out,"\n"."node".$_GET["id"]."\"");
	print("Vielen Dank! Nach der n&auml;chsten Aktualisierung wird dieser Knoten nicht mehr angezeigt.");
}
fclose($out);
?>
</body>
</html>