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

mysql_set_charset("utf8");

$ip=getIp();
$query = "DELETE FROM `hits` where ip=\"".$ip."\" and time > date(now())";
echo $query."<br/>";
mysql_query($query) or die ("MySQL-Error: ".mysql_error());
echo mysql_affected_rows() ." entries removed";

$hits=mysql_query("SELECT * FROM hits ORDER BY time DESC LIMIT 100") or die ("MySQL-Error: ".mysql_error());

echo '<style type="text/css">tr:nth-child(even){background-color:#f2f2f2;}tr{white-space:nowrap;}</style>';
echo '<table>';

while($hit=mysql_fetch_assoc($hits)) {
	echo "<tr><td>".$hit['time']."</td><td>".$hit['ip']."</td><td>".$hit['id']."</td><td>".$hit['ua']."</td><td>".$hit['referrer']."</td></tr>\n";
}

echo '</table>';

?>
