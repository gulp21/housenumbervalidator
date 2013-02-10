<?

// in case do not track is enabled, save a shortened ip
function getIp() {
	$addr=$_SERVER['REMOTE_ADDR'];
	if(strlen($addr)<3) return "NAN";
	if(isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT']==1) {
		return "DNT".$addr[0].$addr[1].$addr[2].".".$addr[strlen($addr)-2].$addr[strlen($addr)-1];
	} else {
		return $addr;
	}
}

include("../connect.php");

header("Content-Type: text/html; charset=UTF-8");

mysql_set_charset("utf8");

$ip=getIp();
$query = "DELETE FROM `hits` where ip=\"".$ip."\" and time >= date(now())";
echo $query."<br/>";
mysql_query($query) or die ("MySQL-Error: ".mysql_error());
echo mysql_affected_rows() ." entries removed";

$hits=mysql_query("SELECT * FROM hits ORDER BY time DESC LIMIT 100") or die ("MySQL-Error: ".mysql_error());

echo '<style type="text/css">
table {border-spacing: 0px;}
tr:nth-child(even) {background-color:#f2f2f2;}
tr {white-space:nowrap;}
td {padding: 0px 3px;}
td:nth-child(3) {max-width:900px;overflow:hidden;text-overflow:hidden;}
td:first-child:last-child {max-width:800px;overflow:hidden;text-overflow:hidden;padding-left:20px;}
</style>';

$lastDate="";

echo "<table>\n";

while($hit=mysql_fetch_assoc($hits)) {
	if(substr($hit['time'],0,10)!=$lastDate) {
		echo "<tr><td colspan=\"4\"><hr/></td></tr>\n";
		$lastDate=substr($hit['time'],0,10);
	}
	echo "<tr><td>".$hit['time']." ".$hit['ip']."</td><td>".$hit['id']."</td><td>".$hit['ua']."</td>";
	if($hit['referrer']!="DNT" && $hit['referrer']!="NONE" && $hit['referrer']!="NULL") {
		echo "<td></td></tr><tr><td colspan=\"5\">".$hit['referrer']." "."</td></tr>\n";
	} else {
		echo "<td>".$hit['referrer']." "."</td></tr>\n";
	}
}

echo '</table>';

?>
