<html>
<body>
<?

include("connect.php");
mysql_set_charset("utf8");

$mail=mysql_real_escape_string($_GET["mail"]);

if(!preg_match('/.+\@.+\..+/i',$mail)) die("Anfrage NICHT erfolgreich ausgef&uuml;hrt (ERR50).");

if($_GET["register"]=="true") {
	
	mysql_query("insert into `mails` values ('$mail')") or die("Anfrage NICHT erfolgreich ausgef&uuml;hrt (ERR0A).");
	
	mail($mail, "Ein korrigierter Fehler am Tag", "Sie (oder jemand anders) haben sich auf http://gulp21.bplaced.net/osm/housenumbervalidator bei \"Ein korrigierter Fehler am Tag\" angemeldet. Sie werden nun (fast) täglich nach jeder Datenbank-Aktualisierung eine E-Mail erhalten.\nWenn Sie keine E-Mails mehr erhalten wollen, nutzen Sie den Link \"Ein korrigierter Fehler am Tag\" auf der oben genannten Seite zur Abmeldung. Sollten Sie weiterhin unerwünschte Nachrichten erhalten, melden Sie dies bitte (Kontaktmöglichkeiten stehen auf der oben genannten Seite zur Verfügung).", "Content-Type: text/plain; charset=\"utf-8\"\nFrom: housenumbervalidator <support.gulp21@googlemail.com>");
	
	echo "An $mail wurde eine Best&auml;tigungs-E-Mail geschickt.";
	
} else {
	
	$d=mysql_query("DELETE FROM mails WHERE mail=\"$mail\"") or die("Anfrage NICHT erfolgreich ausgef&uuml;hrt (ERR0D).");
	
	if(mysql_affected_rows()>0)
		echo "An $mail werden keine E-Mails mehr geschickt werden.";
	else
		echo "Anfrage NICHT erfolgreich ausgef&uuml;hrt (ERR1D).";
	
}

?>
</body>
</html>