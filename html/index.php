<!DOCTYPE HTML>
<html style="height:99%">
<head>
	<title>housenumbervalidator</title>
	<style type="text/css">
		
		[id^="OL_Icon_"], [id^="OpenLayers.Geometry.Point"] {
			cursor: pointer;
			opacity: .75 !important;
		}
		
		#footer {
			position: absolute;
			bottom: 18px;
			left: 10px;
			z-index: 1000;
			opacity: .7;
			background: white;
		}
		
		#reportdiv {
			display: none;
			position: absolute;
			top: 5px;
			left: 5px;
			z-index: 2000;
			background: white;
		}
		
		#reportframe {
			position: absolute;
			top: 60px;
			z-index: 2000;
			border: none;
			background: white;
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
	</style>
</head>
<body style="height:99%;overflow:hidden;">
	
	<!--[if lt IE 8]>
	<p style="color:red;font-size:20px;max-width:80%">Ihr Browser ist alt, unsicher und langsam!</p>
	<p style="font-size:20px;max-width:80%">Sie benutzen eine sehr alte Version des Internet Explorers, welche unsicher und langsam ist und nicht in der Lage ist, diese und andere Webseiten richtig darzustellen.<br/>
	Laden Sie sich <a href="http://www.microsoft.com/windows/internet-explorer/" target="_blank">die aktuelle Version des Internet Explorers</a> konstelos herunter, benutzen Sie einen anderen kostenlosen Browser, z.B. <a href="http://www.mozilla.org/firefox/" target="_blank">Mozilla Firefox</a> oder <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a> oder installieren Sie ein anderes Betriebssystem, z.B. das freie <a href="http://ubuntuusers.de" target="_blank">Ubuntu</a>.</p>
	<![endif]-->
	
	<div style="height:100%" id="mapdiv"></div>
	<iframe style="display:none;" id="josmframe" src="about:blank"></iframe>
	<iframe style="display:none;" id="reportframe" src="about:blank"></iframe>
	<iframe style="display:none;" id="counterframe" src="../counter.php?id=hnrv"></iframe>
	<script src="http://www.openlayers.org/api/OpenLayers.js"></script>
	<script src="http://www.openstreetmap.org/openlayers/OpenStreetMap.js"></script>
	<script>
		map = new OpenLayers.Map("mapdiv",
		{
			controls: [
				new OpenLayers.Control.Navigation(),
				new OpenLayers.Control.PanZoomBar(),
				new OpenLayers.Control.LayerSwitcher({'ascending':false}),
				new OpenLayers.Control.Permalink()
			]
                });
		
		var mapnikMap = new OpenLayers.Layer.OSM.Mapnik("Mapnik",
		{
			transitionEffect: 'resize'
		});
		map.addLayer(mapnikMap);

		var dupes = new OpenLayers.Layer.Vector("Dupes", {
			strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1})],
			protocol: new OpenLayers.Protocol.HTTP({
				url: "./get_dupes.php",
				format: new OpenLayers.Format.Text()
			})
		});
		map.addLayer(dupes);
		
		
		var probl = new OpenLayers.Layer.Vector("Problematic", {
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
		
		function showPosition(lat, lon) {
			var size = new OpenLayers.Size(16,16);
			var offset = new OpenLayers.Pixel(-8,-8);
			var icon = new OpenLayers.Icon('pin.png',size,offset);
			var lonLat = new OpenLayers.LonLat(lon,lat)
			.transform(
				new OpenLayers.Projection("EPSG:4326"), // transform from WGS 1984
				map.getProjectionObject() // to Spherical Mercator Projection
			);
			marker = new OpenLayers.Marker(lonLat,icon);
			marker.events.register('mousedown', marker, function(evt) { markers.removeMarker(this); this.destroy(); });
			markers.addMarker(marker);
			markers.setVisibility(true);
		}
		
		function report() {
			if(document.getElementById('reportdiv').style.display=='block') {
				document.getElementById('reportdiv').style.display='none';
				document.getElementById('reportframe').style.display='none';
			} else {
				document.getElementById('reportdiv').style.display='block';
				document.getElementById('reportframe').style.display='block';
			}
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
	echo "<span style=\"font-weight:bold;\">$date_current</span> ($hnr_current $hnr_diff Hausnummern, $dupes_current $dupes_diff Duplikate, $probl_current $probl_diff problematisch$date_old)";
	?>
	<br/>
	<span style="font-weight:bold">Maximal 1800 angezeigt! Heranzoomen, um alle Probleme im angezeigten Ausschnitt zu sehen.</span>
	<br/>
	Dupes: <img src="pin_red.png" alt="red square"/> Nodes, <img src="pin_blue.png" alt="blue square"/> Ways &dash;
	Problematic:<!-- <img src="pin_circle.png" alt="black circle"/> Incomplete,--> <img src="pin_circle_red.png" alt="red circle"/> Street, <img src="pin_circle_blue.png" alt="blue circle"/> Country/City/Postcode (enable layer with +-button)
	<br/>
	<a href="https://github.com/gulp21" target="_blank">Source</a> &dash;
	<a href="http://forum.openstreetmap.org/viewtopic.php?id=12669" target="_blank">Forum</a> &dash;
	<span style="font-weight:bold;"><a href="http://wiki.openstreetmap.org/wiki/User:Gulp21/housenumbervalidator" target="_blank">Hilfe (Wiki)</a></span> &dash;
	<a href="http://gulp21.github.com/" target="_blank">mehr&hellip;</a> &dash;
	Kontakt: <a href="http://www.openstreetmap.org/message/new/gulp21" target="_blank">&uuml;ber OSM</a>,
	<a href="#" onclick="alert(unescape('support[dot]gulp21 (%E4t) googlemail[dot]com'));">E-Post</a> &dash;
	<a href="#" onclick="report();" style="color:red;font-weight:bold;">Fehlalarm melden</a> &dash;
	&copy;&nbsp;<a href="http://osm.org" target="_blank">OpenStreetMap</a> and contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/" target="_blank">CC&#8209;BY&#8209;SA</a>
	</div>
	<div id="reportdiv">
	Bitte geben Sie die ID ein:
	<form action="report.php" method="get" target="reportframe">
	<input type="text" size="17" name="id"/>
	<input type="checkbox" name="way" value="true"/>Das ist ein Weg (blaues Quadrat)
	<input type="submit" value="Absenden"/>
	</form>
	<small>Bitte diese Funktion nicht verwenden, wenn der Fehler zwischenzeitlich korrigiert wurde!</small>
	</div>
</body>
</html>