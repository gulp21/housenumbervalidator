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

function getReferer() {
	if(!isset($_GET["ref"])) {
		return "NULL";
	} else if($_GET["ref"]=="") {
		return "NONE";
	} else if(isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT']==1) {
		return "DNT";
	} else  {
		return $_GET["ref"];
	}
}

include("housenumbervalidator/connect.php");

mysql_set_charset("utf8");

$out = fopen("counter.txt", "a");
$timestamp = date("ymd:H",time());
$ip = mysql_real_escape_string(getIp());
$id = mysql_real_escape_string($_GET["id"]);
$ua = mysql_real_escape_string($_SERVER[HTTP_USER_AGENT]);
$referer = mysql_real_escape_string(getReferer());
fputs($out,$ip."\t".$id."\t".$timestamp."\t".$ua."\t".$referer."\n");
fclose($out);

$time = date('y/m/d H:00:00',time());

$query = "insert into `hits` values (\"".$ip."\", \"".$id."\", \"".$time."\", \"".$ua."\", \"".$referer."\")";
// echo $query;
mysql_query($query); //or die ("MySQL-Error: ".mysql_error());

?>
