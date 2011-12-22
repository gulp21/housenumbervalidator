<html>
<body>
<?
$out = fopen("reports.txt", "a");
if (!$out) {
    print("Err");
    exit;
}
fputs($out,$_GET["id"]."\t".$_GET["way"]."\n");
print("Vielen Dank! Nach der n&auml;chsten Aktualisierung wird dieser Weg/Knoten nicht mehr angezeigt.");
fclose($out);
?>
</body>
</html>