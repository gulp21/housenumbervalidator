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
$query = "DELETE FROM `hits` where ip=\"".$ip."\" and time > date(now())";
echo $query."<br/>";
mysql_query($query) or die ("MySQL-Error: ".mysql_error());
echo mysql_affected_rows() ." entries removed";

$hits=mysql_query("SELECT * FROM hits ORDER BY time DESC LIMIT 100") or die ("MySQL-Error: ".mysql_error());

echo '<style type="text/css">div:nth-child(even){background-color:#f2f2f2;}div{white-space:nowrap;position:relative;}span:nth-child(2){position:absolute;left:245px;}span:nth-child(3){position:absolute;left:310px;}span:nth-child(4){position:absolute;right:0px;}span:nth-child(5){padding-left:310px;}</style>';

$lastDate="";
while($hit=mysql_fetch_assoc($hits)) {
	if(substr($hit['time'],0,10)!=$lastDate) {
		echo "<hr/>\n";
		$lastDate=substr($hit['time'],0,10);
	}
	echo "<div><span>".$hit['time']." ".$hit['ip']."</span><span>".$hit['id']."</span><span>".$hit['ua']."</span>";
	if($hit['referrer']!="DNT" && $hit['referrer']!="NONE" && $hit['referrer']!="NULL") {
		echo "<br/><span>".$hit['referrer']." "."</span></div>\n";
	} else {
		echo "<span>".$hit['referrer']." "."</span></div>\n";
	}
}

echo '</table>';

?>
