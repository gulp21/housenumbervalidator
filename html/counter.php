<?

// in case do not track is enabled, save a shortened ip
function ip() {
	$addr=$_SERVER['REMOTE_ADDR'];
	if(strlen($addr)<3) return "NAN";
	if(isset($_SERVER['HTTP_DNT']) && $_SERVER['HTTP_DNT']==1) {
		return "DNT".$addr[0].$addr[1].$addr[2].".".$addr[strlen($addr)-2].$addr[strlen($addr)-1];
	} else {
		return $addr;
	}
}

$out = fopen("counter.txt", "a");
$timestamp = date("ymd:H",time());
fputs($out,ip()."\t".$_GET["id"]."\t".$timestamp."\t".$_SERVER['HTTP_USER_AGENT']."\n");
fclose($out);
?>
