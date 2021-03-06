<!DOCTYPE HTML>
<html>
<head>
	<meta charset="UTF-8"/>
	<title>housenumbervalidator</title>
	<link rel="icon" href="logo.png" type="image/png">
	<link rel="stylesheet" type="text/css" href="style.css"/>
</head>
<body>
	
	<!--[if lt IE 8]>
	<p style="background:white;color:red;font-size:20px;width:60%;z-index:1100;position:relative;left:280px;">Ihr Browser ist sehr alt, unsicher und langsam!</p>
	<![endif]-->
	<!--[if lt IE 9]>
	<p style="background:white;font-size:20px;width:60%;z-index:1100;position:relative;left:280px;">Sie benutzen eine alte Version des Internet Explorers, die langsam ist und nicht in der Lage ist, diese und andere Webseiten richtig darzustellen.<br/>
	Laden Sie sich <a href="http://www.microsoft.com/windows/internet-explorer/" target="_blank">die aktuelle Version des Internet Explorers</a> kostenlos herunter, benutzen Sie einen anderen kostenlosen Browser, z.&nbsp;B. <a href="http://www.mozilla.org/firefox/" target="_blank">Mozilla Firefox</a> oder <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a>, oder installieren Sie ein anderes Betriebssystem, z.&nbsp;B. das freie <a href="http://ubuntuusers.de" target="_blank">Ubuntu</a>.</p>
	<![endif]-->
	
	<div id="sidebar" class="visibleBar">
		Letzte Aktualisierung: 
		<?php
		include("connect.php");
		
		$stats=mysql_query("SELECT * FROM `stats` ORDER BY date DESC LIMIT 1");
		
		while($stat=mysql_fetch_assoc($stats)) {
			$date_current=$stat['date'];
			$hnr_current=$stat['housenumbers'];
			$dupes_current=$stat['dupes'];
			$prob_current=$stat['problematic'];
		}
		?>
		<span class="bold"><?php echo $date_current ?></span><br/>
		<br/>
		<span class="bold">Statistik</span> (nur Deutschland)<br/>
		<?php echo $hnr_current ?>&nbsp;Hausnummern, <?php echo $dupes_current ?>&nbsp;Duplikate, <?php echo $prob_current ?>&nbsp;problematisch<br/>
		<a href="stat.php" target="_blank">mehr Statistiken</a><br/>
		<br/>
		<span id="exportGpx" title="Bestimmten Fehlertyp als .gpx-Datei exportieren">Als .gpx exportieren ▾
			<span>
			<a href="#" onclick="exportAsGpx(10);">Duplikate (sehr nah)</a><br/>
			<a href="#" onclick="exportAsGpx(11);">Duplikate (exakt)</a><br/>
			<a href="#" onclick="exportAsGpx(12);">Duplikate (ähnlich)</a><br/>
			<a href="#" onclick="exportAsGpx(20);">Problematisch (einfach)</a><br/>
			<a href="#" onclick="exportAsGpx(21);">Problematisch (komplizierter)</a><br/>
			</span>
		</span><br/>
		<a href="#" onclick="showAsList();" title="Duplikate als einfache Liste anzeigen">Als Liste exportieren</a><br/>
		<a href="#" onclick="showAreaStat();" title="die letzten Bearbeiter der im aktuellen Bereich liegenden problematischen und doppelten Hausnummern anzeigen">Bereichsstatistik</a><br/>
		<a href="#" onclick="openOsmi()" title="aktuellen Bereich in der Adress-Ansicht des OSM Inspectors anzeigen">Bereich im OSMI anzeigen</a><br/>
		<br/>
		<span class="bold">Legende</span><br/>
		<span title="Es gibt zwei Objekte, die dieselben Adressdaten tragen. Das zweite Objekt wird nach einem Klick auf [zeigen] markiert.">Duplikate</span>:<br/>
		<span title="Das Objekt und sein Duplikat sind sehr nah beieinander (oder übereinander). Dies ist oft auf einen doppelten Upload zurückzuführen oder auf vergessenes Löschen eins Objekts. Dieser Fehler ist einfach behebbar."><img src="pin_pink.png" alt="pink square"/>&nbsp;Sehr&nbsp;nah</span>,
		<span title="Das Objekt und sein Duplikat besitzten exakt dieselben Adressdaten."><img src="pin_red.png" alt="red square"/>&nbsp;Exakt,</span>
		<span title="Das Objekt und sein Duplikat besitzten exakt dieselben Adressdaten, aber bei einem der beiden Objekte sind Adresseigenschaften gesetzt, die bei dem anderen Objekt fehlen. Daher könnte es zu mehr Fehlalarmen kommen."><img src="pin_blue.png" alt="blue square"/>&nbsp;Ähnlich</span><br/>
		<span title="Mindestens eine Adresseigenschaft hat einen Wert, der fehlerhaft aussieht.">Problematisch</span>:<br/>
		<span title="Der Fehler kann einfach, ohne Ortskenntnis behoben werden."><img src="pin_circle_red.png" alt="red circle"/>&nbsp;EasyFix</span>,
		<span title="Der Fehler kann i.&nbsp;d.&nbsp;R. nicht einfach ohne Ortskenntnis behoben werden oder wird automatisch von einem Bot (z.&nbsp;B. xybot) behoben."><img src="pin_circle_blue.png" alt="blue circle"/>&nbsp;Komplizierter/Botaufgabe</span><br/>
		<br/>
		<a href="#" onclick="showReport();" class="bold" style="color:red;">Fehlalarm melden</a><br/>
		<a href="#" onclick="showOnePerDay();" class="bold" style="color:green;" title="Mithelfen, die Karte zu verbessern, indem jeden Tag ein Problem behoben wird.">Ein korrigierter Fehler am Tag</a><br/>
		<br/>
		<span class="bold">Links</span><br/>
		<a href="https://github.com/gulp21" target="_blank">Quellcode</a>,
		<a href="http://forum.openstreetmap.org/viewtopic.php?id=12669" target="_blank">Forum</a>,
		<span class="bold"><a href="http://wiki.openstreetmap.org/wiki/User:Gulp21/housenumbervalidator" target="_blank">Hilfe (Wiki)</a></span>,
		<a href="http://gulp21.github.com/" target="_blank">mehr&hellip;</a><br/>
		<br/>
		<span class="bold">Kontakt</span><br/>
		<a href="http://www.openstreetmap.org/message/new/gulp21" target="_blank">über OSM</a>,
		<a href="#" onclick="alert(unescape('support[dot]gulp21 (%E4t) googlemail[dot]com'));">E-Post</a>
	</div>
	<a id="toggleSidebar" href="#" onclick="toggleSidebar()">ausblenden</a>
	
	<div id="middleBar" class="hiddenBar">
	<a href="#" onclick="hideMiddleBar()" id="closeButton" title="schließen"><img src="theme/default/img/close.gif" alt="[close]"/></a>
	<div id="middleBarContents">
		<div id="reportdiv">
			<div class="bold">Fehlalarm melden</div>
			Nutzen Sie diese Funktion, wenn die doppelten oder &quot;fehlerhaften&quot; Hausnummern tatsächlich so in der Realität existieren.<br/>
			Klicken Sie zunächst im Popup auf den grünen Haken (Fehler als behoben kennzeichnen);<br/>
			dadurch werden die untenstehenden Felder ausgefüllt. Klicken Sie anschließend auf &quot;Absenden&quot;.
			<form action="report.php" method="get" target="reportframe">
			<input type="text" size="17" name="id" readonly/>
			<input type="checkbox" name="type_u" value="true" disabled/>Das ist ein Weg
			<input type="hidden" name="type" value="1"/>
			<input type="submit" value="Absenden"/>
			</form>
			<span class="small">Bitte diese Funktion <span class="bold">nicht</span> verwenden, wenn der Fehler <span class="bold">zwischenzeitlich korrigiert</span> wurde (also nur bei false positives verwenden)!</span>
			<iframe id="reportframe" src="about:blank"></iframe>
		</div>
		<div id="oneperdaydiv">
			<div class="bold">Sie wollen regelmäßig zur Verbesserung der Daten beitragen?</div>
			Wenn Sie sich hier registrieren, werden Sie (fast) täglich eine E-Mail mit einem Link zu einem Fehler bekommen, den Sie korrigieren können.<br/>
			<span class="small">Wenn Sie sich abmelden wollen, tragen Sie Ihre E-Mailadresse in das Textfeld ein und klicken Sie auf &quot;Anmelden&quot;, ohne die Checkbox vorher aktiviert zu haben.</span>
			<form action="register.php" method="get" target="oneperdayframe">
			<input type="text" size="17" name="mail"/>
			<input type="checkbox" name="register" value="true"/>Ich möchte mich anmelden. Die angegebene E-Mail-Adresse gehört mir.<br/>Die E-Mail-Adresse wird nur zum Versand der E-Mails (max. 1/d) verwendet.<br/>
			<input type="submit" value="Anmelden"/>
			</form>
			<iframe id="oneperdayframe" src="about:blank"></iframe>
		</div>
		<div id="areastatdiv">
			<iframe id="areastatframe" src="about:blank"></iframe>
		</div>
		<div id="listdiv">
			<div>Duplikate im angezeigten Gebiet:</div>
			<iframe id="listframe" src="about:blank"></iframe>
		</div>
	</div>
	</div>
	
	<div id="zoominfo" class="hidden">Evtl. werden nicht alle Probleme angezeigt. Heranzoomen,<br/> um alle Probleme im dargestellten Ausschnitt zu sehen.</div>
	
	<div id="mapdiv"></div>
	<iframe style="display:none;" id="josmframe" src="about:blank" name="josmframe"></iframe>
	<script type="text/javascript">
		document.write("<iframe style=\"display:none;\" id=\"counterframe\" src=\"../counter.php?id=hnrv&ref="  + document.referrer.replace(/\&/g,"%26") + "\"></iframe>");
		<?php
		echo "var lon=9.1;\n";
		echo "var lat=51.32;\n";
		echo "var zoom=6;\n";
		$type=0;
		if($_GET['type']==1) {
			$type=1;
		}
		if(is_numeric($_GET['id'])) {
			include_once("connect.php");
			mysql_set_charset("utf8");
			$obj=mysql_query("SELECT lat,lon FROM problematic WHERE id=".$_GET['id']." AND type=".$type." UNION SELECT lat,lon FROM dupes WHERE id=".$_GET['id']." AND type=".$type) or die ("MySQL-Error: ".mysql_error());
			if($obj=mysql_fetch_assoc($obj)) {
				echo "lon=".$obj['lon'].";\n";
				echo "lat=".$obj['lat'].";\n";
				echo "zoom=18;\n";
				// TODO also open the popup
			}
		}
		?>
	</script>
	<script src="OpenLayers.js"></script>
	<script src="OpenStreetMap.js"></script>
	<script src="javascript.js"></script>
	<a href="http://gulp21.github.com/qeodart_de.html" target="_blank" class="ad" id="adDart" style="display:none">
		<img src="qeodart.png" alt="QeoDart Icon"/>
		<b>QeoDart</b><br/>
		das freie Geographie-Lernspiel<br/>
		für Linux &amp; Windows
	</a>
	<a href="http://languagetool.org/de" target="_blank" class="ad" id="adLt" style="display:none;">
		<img src="LanguageToolBig.png" alt="LT Icon"/>
		<b>LanguageTool</b><br/>
		freie Grammatik- und Stilprüfung<br/>
		für LibreOffice, OpenOffice uvm.
	</a>
	<a href="https://addons.mozilla.org/firefox/addon/languagetoolfx/" target="_blank" class="ad" id="adLtFx" style="display:none;">
		<img src="lt-ext.png" alt="LT Icon"/>
		<b>LanguageToolFx</b><br/>
		freie Grammatik- und Stilprüfung<br/>
		für Firefox
	</a>
	<script type="text/javascript">
		var rnd=Math.random();
		if(Math.random()>.6)
			document.getElementById("adDart").style.display='block';
		else if(Math.random()<.2 && navigator.userAgent.indexOf("Firefox")>-1)
			document.getElementById("adLtFx").style.display='block';
		else
			document.getElementById("adLt").style.display='block';
	</script>
</body>
</html>
