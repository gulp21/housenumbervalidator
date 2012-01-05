<?
$out = fopen("counter.txt", "a");
$timestamp = date("ymd:H",time());
$uhrzeit = date("H:i",$timestamp);
fputs($out,$_SERVER['REMOTE_ADDR']." ".$_GET["id"]." ".$timestamp." ".$_SERVER['HTTP_USER_AGENT']."\n");
fclose($out);
?>
