<!DOCTYPE HTML>
<html style="height: 99%; font-family: 'Times New Roman', serif;">
<head>
	<title>housenumbervalidator</title>
	<style type="text/css">
		
		[id^="OpenLayers.Control.Attribution"] {
			top: 0px;
			right: 0px !important;
			max-height: 20px;
			opacity: .7;
			background: white;
			padding: 0px 3px 0px 5px;
		}
		
		[id^="OL_Icon_"], [id^="OpenLayers.Geometry.Point"] {
			cursor: pointer;
			opacity: .75 !important;
		}
		
		[id^="OpenLayers.Layer.Markers"] img {
			-webkit-animation: shrink .7s;
			-moz-animation: shrink .7s;
			-o-animation: shrink .7s;
			-ms-animation: shrink .7s;
			animation: shrink .7s;
		}
		
		@-webkit-keyframes shrink {
			from {
				width: 1000px;
				height: 1000px;
				margin-top: -500px;
				margin-left: -500px;
				opacity: .5;
			}
			to {
				width: 16px;
				height: 16px;
				margin-top: 0px;
				margin-left: 0px;
				opacity: .75;
			}
		}
		@-moz-keyframes shrink {
			from {
				width: 1000px;
				height: 1000px;
				margin-top: -500px;
				margin-left: -500px;
				opacity: .5;
			}
			to {
				width: 16px;
				height: 16px;
				margin-top: 0px;
				margin-left: 0px;
				opacity: .75;
			}
		}
		@-o-keyframes shrink {
			from {
				width: 1000px;
				height: 1000px;
				margin-top: -500px;
				margin-left: -500px;
				opacity: .5;
			}
			to {
				width: 16px;
				height: 16px;
				margin-top: 0px;
				margin-left: 0px;
				opacity: .75;
			}
		}
		@-ms-keyframes shrink {
			from {
				width: 1000px;
				height: 1000px;
				margin-top: -500px;
				margin-left: -500px;
				opacity: .5;
			}
			to {
				width: 16px;
				height: 16px;
				margin-top: 0px;
				margin-left: 0px;
				opacity: .75;
			}
		}
		@keyframes shrink {
			from {
				width: 1000px;
				height: 1000px;
				margin-top: -500px;
				margin-left: -500px;
				opacity: .5;
			}
			to {
				width: 16px;
				height: 16px;
				margin-top: 0px;
				margin-left: 0px;
				opacity: .75;
			}
		}
		
		#footer {
			position: absolute;
			bottom: 18px;
			left: 10px;
			z-index: 1000;
			opacity: .7;
			background: white;
			font-size: 16px;
		}
		
		@media (max-width: 800px) {
			#footer {
				font-size: 14px;
			}
		}
		
		@media (max-width: 500px) {
			#footer {
				font-size: 11px;
			}
		}
		
		@media (max-width: 300px) {
			#footer {
				font-size: 8px;
			}
		}
		
		@media (max-width: 900px) {
			.ad {
				visibility: hidden;
			}
		}
		
		#reportdiv, #oneperdaydiv {
			display: none;
			position: absolute;
			top: 5px;
			left: 5px;
			z-index: 2000;
			background: white;
			padding-right: 22px;
		}
		
		#reportframe {
			position: absolute;
			top: 175px;
			z-index: 2000;
			border: none;
			background: white;
		}
		
		.closediv {
			position: absolute;
			right: 4px;
			top: 2px;
		}
		
		[id^="OL_Icon_"], [id^="OpenLayers.Geometry.Point"], #footer {
			-moz-transition: opacity .2s ease-in .1s;
			-webkit-transition: opacity .2s ease-in .1s;
			-ms-transition: opacity .2s ease-in .1s;
			-o-transition: opacity .2s ease-in .1s;
			transition: opacity .2s ease-in .1s;
		}
		
		/*[id="OpenLayers.Map_6_OpenLayers_Container"] > div:nth-child(4) > [id^="OL_Icon_"] {
			opacity: .4;
		}*/
		
		[id^="OL_Icon_"]:hover, [id^="OpenLayers.Geometry.Point"]:hover {
			opacity: 1 !important;
		}
		
		#footer:hover {
			opacity: .9;
		}
		
		[id^="OL_Icon_"]:hover, [id^="OpenLayers.Geometry.Point"]:hover, #footer:hover {
			-moz-transition: opacity .4s ease-in .1s;
			-webkit-transition: opacity .4s ease-in .1s;
			-ms-transition: opacity .4s ease-in .1s;
			-o-transition: opacity .4s ease-in .1s;
			transition: opacity .4s ease-in .1s;
		}
		
		#footer > img {
			max-height: 10px;
			max-width: 10px;
		}
		
		[id*="popup"], #featurePopup {
			opacity: .95 !important;
		}
		
		h2 {
			margin: 0px;
			font-size: large;
		}
		
		tr:nth-child(odd) {
			background-color: #f2f2f2;
		}
		
		table {
			border-spacing: 0px;
		}
		
		.broken {
			color: red;
		}
		
		.ad {
			position: absolute;
			right: 10px;
			bottom: 45px;
			z-index: 999;
			white-space:nowrap;
			width: 250px;
			text-decoration: none;
			color: black;
			opacity: .7;
		}
		
		.ad:hover {
			opacity: 1;
		}
		
		.ad > img {
			max-height: 48px;
			width: auto;
			float: left;
			margin: 5px;
		}
		
		a img {
			border: none;
		}
		
		a[href^="report.php?id="] {
			text-decoration: none;
			color: green;
		}
	</style>
</head>
<body style="height:99%;overflow:hidden;">
	
	<!--[if lt IE 8]>
	<p style="color:red;font-size:20px;max-width:80%">Ihr Browser ist sehr alt, unsicher und langsam!</p>
	<![endif]-->
	<!--[if lt IE 9]>
	<p style="font-size:20px;max-width:80%">Sie benutzen eine alte Version des Internet Explorers, die langsam ist und nicht in der Lage ist, diese und andere Webseiten richtig darzustellen.<br/>
	Laden Sie sich <a href="http://www.microsoft.com/windows/internet-explorer/" target="_blank">die aktuelle Version des Internet Explorers</a> kostenlos herunter, benutzen Sie einen anderen kostenlosen Browser, z.B. <a href="http://www.mozilla.org/firefox/" target="_blank">Mozilla Firefox</a> oder <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a> oder installieren Sie ein anderes Betriebssystem, z.B. das freie <a href="http://ubuntuusers.de" target="_blank">Ubuntu</a>.</p>
	<![endif]-->
	
	<div style="height:100%" id="mapdiv"></div>
	<iframe style="display:none;" id="josmframe" src="about:blank" name="josmframe"></iframe>
	<iframe style="display:none;" id="reportframe" src="about:blank"></iframe>
	<script type="text/javascript">
		document.write("<iframe style=\"display:none;\" id=\"counterframe\" src=\"../counter.php?id=hnrv&ref="  + document.referrer.replace(/\&/g,"%26") + "\"></iframe>");
	</script>
	<script src="OpenLayers.js"></script>
	<script src="OpenStreetMap.js"></script>
	<script type="text/javascript">
		// parts taken from openlayers.org/dev/examples
		
		map = new OpenLayers.Map("mapdiv",
		{
			controls: [
				new OpenLayers.Control.Navigation({documentDrag: true/*, dragPanOptions: {enableKinetic: true}*/}),
				new OpenLayers.Control.PanZoomBar(),
				new OpenLayers.Control.LayerSwitcher({'ascending':false}),
				new OpenLayers.Control.Permalink(),
				new OpenLayers.Control.Attribution()
			],
			projection: new OpenLayers.Projection("EPSG:900913"),
			displayProjection: new OpenLayers.Projection("EPSG:4326")
		});
		
		var mapnikMap = new OpenLayers.Layer.OSM.Mapnik("Mapnik",
		{
			transitionEffect: 'resize',
			attribution: '&copy;&nbsp;<a href="http://osm.org" target="_blank">OpenStreetMap</a> and contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC&#8209;BY&#8209;SA</a>'
		});
		map.addLayer(mapnikMap);
		
		var mapquestMap = new OpenLayers.Layer.OSM("MapQuest", "http://otile1.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png", 
		{
			transitionEffect: 'resize',
			numZoomLevels: 19,
			attribution: 'Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png">, &copy;&nbsp;<a href="http://osm.org" target="_blank">OpenStreetMap</a> and contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC&#8209;BY&#8209;SA</a>'
		});
		map.addLayer(mapquestMap);
		
		var dupes = new OpenLayers.Layer.Vector("Dupes", {
			projection: map.displayProjection,
			strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1})],
			protocol: new OpenLayers.Protocol.HTTP({
				url: "./get_dupes.php",
				format: new OpenLayers.Format.Text()
			})
		});
		map.addLayer(dupes);
		
		
		var probl = new OpenLayers.Layer.Vector("Problematic", {
			projection: map.displayProjection,
			strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1})],
			protocol: new OpenLayers.Protocol.HTTP({
				url: "./get_problematic.php",
				format: new OpenLayers.Format.Text()
			})
		});
		map.addLayer(probl);

		// Interaction; not needed for initial display.
		selectControl = new OpenLayers.Control.SelectFeature([dupes,probl]);
		map.addControl(selectControl);
		selectControl.activate();
		dupes.events.on({
			'featureselected': onFeatureSelect,
			'featureunselected': onFeatureUnselect
		});
		probl.events.on({
			'featureselected': onFeatureSelect,
			'featureunselected': onFeatureUnselect
		});
		
		var markers = new OpenLayers.Layer.Markers( "Markers", {projection: map.displayProjection} );
		map.addLayer(markers);
		
		//Set start centrepoint and zoom    
		var lonLat = new OpenLayers.LonLat(9.1,51.32)
			.transform(
				new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
				map.getProjectionObject() // to Spherical Mercator Projection
			);
		var zoom=6;
		if (!map.getCenter())
			map.setCenter (lonLat, zoom);
		
		
		// Needed only for interaction, not for the display.
		function onPopupClose(evt) {
			// 'this' is the popup.
			var feature = this.feature;
			if (feature.layer) { // The feature is not destroyed
			selectControl.unselect(feature);
			} else { // After "moveend" or "refresh" events on POIs layer all 
				//     features have been destroyed by the Strategy.BBOX
			this.destroy();
			}
		}
		
		function onFeatureSelect(evt) {
			feature = evt.feature;
			popup = new OpenLayers.Popup.FramedCloud("featurePopup",
						feature.geometry.getBounds().getCenterLonLat(),
						new OpenLayers.Size(100,100),
						"<h2>"+feature.attributes.title + "</h2>" +
						feature.attributes.description,
						null, true, onPopupClose);
			feature.popup = popup;
			popup.feature = feature;
			map.addPopup(popup, true);
		}
		
		function onFeatureUnselect(evt) {
			feature = evt.feature;
			if (feature.popup) {
			popup.feature = null;
			map.removePopup(feature.popup);
			feature.popup.destroy();
			feature.popup = null;
			}
		}
		
		var marker = null;
		
		function showPosition(lat, lon) {
			var size = new OpenLayers.Size(16,16);
			var offset = new OpenLayers.Pixel(-8,-8);
			var icon = new OpenLayers.Icon('pin.png',size,offset);
			var lonLat = new OpenLayers.LonLat(lon,lat)
			.transform(
				new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
				map.getProjectionObject() // to Spherical Mercator Projection
			);
			if(marker != null) {
				markers.removeMarker(marker);
				marker.destroy();
			}
			marker = new OpenLayers.Marker(lonLat,icon);
			marker.events.register('mousedown', marker, function(evt) { markers.removeMarker(this); this.destroy(); });
			markers.addMarker(marker);
			markers.setVisibility(true);
		}
		
		function hideOverlays() {
			document.getElementById('reportdiv').style.display='none';
			document.getElementById('oneperdaydiv').style.display='none';
			document.getElementById('reportframe').style.display='none';
		}
		
		function report() {
			if(document.getElementById('reportdiv').style.display=='block') {
				hideOverlays();
			} else {
				document.getElementById('reportdiv').style.display='block';
				document.getElementById('reportframe').style.display='block';
			}
		}
		
		function oneperday() {
			if(document.getElementById('oneperdaydiv').style.display=='block') {
				hideOverlays();
			} else {
				document.getElementById('oneperdaydiv').style.display='block';
				document.getElementById('reportframe').style.display='block';
			}
		}
		
		function openOsmi() {
			var position = map.getCenter().transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326"));
			var osmiWindow = window.open("http://tools.geofabrik.de/osmi/?view=addresses&lon="+position.lon+"&lat="+position.lat+"&zoom="+map.getZoom()+"&overlays=postal_code,no_addr_street,street_not_found,interpolation,interpolation_errors,connection_lines,nearest_points,nearest_roads").focus();
		}
		
		function markAsCorrectedClicked(id, type, layer) {
			document.getElementsByName('id')[0].value=id;
			document.getElementsByName('way_u')[0].checked=type;
			document.getElementsByName('way')[0].value=type;
			map.removePopup(map.popups[0]);
			if(layer=="problematic")
				probl.refresh();
			else if(layer=="dupes")
				dupes.refresh();
			else
				alert("unknown layer " + layer + " (" + id + " " + type + ")");
		}
	</script>
	
	<div id="footer">
	Letzte Aktualisierung: 
	<?php
	include("connect.php");
	
	$stats=mysql_query("SELECT * FROM `stats` ORDER BY date DESC LIMIT 2");
	
	$i=0;
	while($stat=mysql_fetch_assoc($stats)) {
		if($i==0) {
			$i=1;
			$date_current=$stat['date'];
			$hnr_current=$stat['housenumbers'];
			$dupes_current=$stat['dupes'];
			$probl_current=$stat['problematic'];
			$hide=$stat['hide'];
		} else {
			$date_old=$stat['date'];
			$hnr_diff=$hnr_current-$stat['housenumbers'];
			if($hnr_diff==0) $hnr_diff="&plusmn;0";
			else if($hnr_diff>0) $hnr_diff="+".$hnr_diff;
			else $hnr_diff="&minus;".$hnr_diff*-1;
			$dupes_diff=$dupes_current-$stat['dupes'];
			if($dupes_diff==0) $dupes_diff="&plusmn;0";
			else if($dupes_diff>0) $dupes_diff="+".$dupes_diff;
			else $dupes_diff="&minus;".$dupes_diff*-1;
			$probl_diff=$probl_current-$stat['problematic'];
			if($probl_diff==0) $probl_diff="&plusmn;0";
			else if($probl_diff>0) $probl_diff="+".$probl_diff;
			else $probl_diff="&minus;".$probl_diff*-1;
			if(($hide|1)==$hide) $hnr_diff="";
			else $hnr_diff=" [".$hnr_diff."]";
			if(($hide|2)==$hide) $dupes_diff="";
			else $dupes_diff=" [".$dupes_diff."]";
			if(($hide|4)==$hide) $probl_diff="";
			else $probl_diff=" [".$probl_diff."]";
			if($hide==7) $date_old="";
			else $date_old=" [verglichen mit $date_old]";
		}
	}
// 	echo "<span style=\"font-weight:bold;\">$date_current</span> ($hnr_current $hnr_diff Hausnummern, $dupes_current $dupes_diff Duplikate, $probl_current $probl_diff problematisch$date_old)";
	echo "<span style=\"font-weight:bold;\">$date_current</span> ($hnr_current Hausnummern in DE, $dupes_current Duplikate, $probl_current problematisch)";
	?>
	&dash; <a href="stat.php" target="_blank">mehr Statistiken</a>
	<br/>
	<span style="font-weight:bold">Maximal 1800 angezeigt! Heranzoomen, um alle Probleme im angezeigten Ausschnitt zu sehen.</span> <a href="#" onclick="javascript:openOsmi()">im OSMI anzeigen</a>
	<br/>
	Duplikate: <img src="pin_red.png" alt="red square"/> Exakt, <img src="pin_blue.png" alt="blue square"/> &Auml;hnlich &dash;
	Problematisch: <img src="pin_circle_red.png" alt="red circle"/> EasyFix, <img src="pin_circle_blue.png" alt="blue circle"/> Komplizierter od. Botaufgabe &dash;
	<a href="#" onclick="report();" style="color:red;font-weight:bold;">Fehlalarm melden</a>
	<br/>
	<a href="https://github.com/gulp21" target="_blank">Source</a> &dash;
	<a href="http://forum.openstreetmap.org/viewtopic.php?id=12669" target="_blank">Forum</a> &dash;
	<span style="font-weight:bold;"><a href="http://wiki.openstreetmap.org/wiki/User:Gulp21/housenumbervalidator" target="_blank">Hilfe (Wiki)</a></span> &dash;
	<a href="http://gulp21.github.com/" target="_blank">mehr&hellip;</a> &dash;
	Kontakt: <a href="http://www.openstreetmap.org/message/new/gulp21" target="_blank">&uuml;ber OSM</a>,
	<a href="#" onclick="alert(unescape('support[dot]gulp21 (%E4t) googlemail[dot]com'));">E-Post</a> &dash;
	<a href="#" onclick="oneperday();" style="color:green;font-weight:bold;">Ein korrigierter Fehler am Tag</a>
	</div>
	<a href="http://gulp21.github.com/qeodart_de.html" target="_blank" class="ad" id="ad1" style="background-color: rgba(256,168,88,.9);display:none">
		<img src="qeodart.png" alt="QeoDart Icon"/>
		<b>QeoDart</b><br/>
		das freie Geographie-Lernspiel<br/>
		f&uuml;r Linux &amp; Windows
	</a>
	<a href="http://languagetool.org/de" target="_blank" class="ad" id="ad2" style="background-color: rgba(152,184,240,.9);display:none;width:270px;">
		<img src="LanguageToolBig.png" alt="LT Icon"/>
		<b>LanguageTool</b><br/>
		freie Grammatik- und Stilpr&uuml;fung<br/>
		f&uuml;r LibreOffice und OpenOffice.org
	</a>
	<script type="text/javascript">
		if(Math.random()>.5)
			document.getElementById("ad1").style.display='block';
		else
			document.getElementById("ad2").style.display='block';
	</script>
	<div id="reportdiv">
		Nutzen Sie diese Funktion, wenn die doppelten oder &quot;fehlerhaften&quot; Hausnummern tats&auml;chlich so in der Realit&auml;t existieren.<a href="#" onclick="javascript:hideOverlays();" class="closediv"><img src="theme/default/img/close.gif" alt="[close]"/></a><br/>
		Klicken Sie zun&auml;chst im Popup auf den gr&uuml;nen Haken (Fehler als behoben kennzeichnen);<br/>
		dadurch werden die untenstehenden Felder ausgef&uuml;llt. Klicken Sie anschlie&szlig;end auf &quot;Absenden&quot;.
		<form action="report.php" method="get" target="reportframe">
		<input type="text" size="17" name="id" readonly/>
		<input type="checkbox" name="way_u" value="true" disabled/>Das ist ein Weg (blaues Quadrat)
		<input type="hidden" name="way" value="1"/>
		<input type="submit" value="Absenden"/>
		</form>
		<small>Bitte diese Funktion nicht verwenden, wenn der Fehler zwischenzeitlich korrigiert wurde (also nur bei false positives verwenden)!</small>
	</div>
	<div id="oneperdaydiv">
		<b>Sie wollen regelm&auml;&szlig;ig zur Verbesserung der Daten beitragen?</b><a href="#" onclick="javascript:hideOverlays();" class="closediv"><img src="theme/default/img/close.gif" alt="[close]"/></a><br/>
		Wenn Sie sich hier registrieren, werden Sie (fast) t&auml;glich<br/>
		eine E-Mail mit einem Link zu einem Fehler bekommen, den Sie korrigieren k&ouml;nnen.<br/>
		<small>Wenn Sie sich abmelden wollen, tragen Sie Ihre E-Mailadresse in das Textfeld ein<br/>
		und klicken Sie auf &quot;Anmelden&quot;, ohne die Checkbox vorher aktiviert zu haben.</small>
		<form action="register.php" method="get" target="reportframe">
		<input type="text" size="17" name="mail"/>
		<input type="checkbox" name="register" value="true"/>Ich m&ouml;chte mich anmelden. Die angegebene E-Mail-Adresse geh&ouml;rt mir.<br/>Die E-Mail-Adresse wird nur zum Versand der E-Mails (max. 1/d) verwendet.<br/>
		<input type="submit" value="Anmelden"/>
		</form>
	</div>
</body>
</html>
