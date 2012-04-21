<!DOCTYPE HTML>
<html style="font-family: 'Times New Roman', serif;">
<head>
	<title>housenumbervalidator &dash; statistic</title>
	<style type="text/css">
		[id^="chart"] {
			height: 300px;
		}
		
		.ad {
			opacity: .7;
			min-height: 42px;
			display: block;
			text-decoration: none;
			color: black;
			clear: left;
			border-radius: 5px;
		}
		
		.ad:hover {
			opacity: 1;
		}
		
		.ad img {
			max-height: 32px;
			width: auto;
			float: left;
			margin: 5px;
		}
		
		a img {
			border: none;
		}
	</style>
</head>
<body>
	
	<!--[if lt IE 8]>
	<p style="color:red;font-size:20px;max-width:80%">Ihr Browser ist sehr alt, unsicher und langsam!</p>
	<![endif]-->
	<!--[if lt IE 9]>
	<p style="font-size:20px;max-width:80%">Sie benutzen eine alte Version des Internet Explorers langsam ist und nicht in der Lage ist, diese und andere Webseiten richtig darzustellen.<br/>
	Laden Sie sich <a href="http://www.microsoft.com/windows/internet-explorer/" target="_blank">die aktuelle Version des Internet Explorers</a> kostenlos herunter, benutzen Sie einen anderen kostenlosen Browser, z.B. <a href="http://www.mozilla.org/firefox/" target="_blank">Mozilla Firefox</a> oder <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a> oder installieren Sie ein anderes Betriebssystem, z.B. das freie <a href="http://ubuntuusers.de" target="_blank">Ubuntu</a>.</p>
	<![endif]-->
	
	<script type="text/javascript">
		document.write("<iframe style=\"display:none;\" id=\"counterframe\" src=\"../counter.php?id=hnrv_stat&ref="  + document.referrer.replace(/\&/g,"%26") + "\"></iframe>");
	</script>
	
	<a href="http://gulp21.bplaced.net/osm/housenumbervalidator/" class="ad" id="ad0" style="background-color:rgba(184,240,168,.9);min-height:0px;padding:5px;margin-bottom:5px;">
		<b>Zum housenumbervalidator</b>
	</a>
	<a href="http://gulp21.github.com/qeodart_de.html" target="_blank" class="ad" id="ad1" style="background-color: rgba(256,168,88,.9);">
		<img src="qeodart.png" alt="QeoDart Icon"/>
		<b>QeoDart</b><br/>
		das freie Geographie-Lernspiel f&uuml;r Linux &amp; Windows
	</a>
	<div id="chart1div"></div>
	<div id="chart2div"></div>
	<div id="chart3div"></div>
	<a href="http://languagetool.org/de" target="_blank" class="ad" id="ad2" style="background-color: rgba(152,184,240,.9);">
		<img src="LanguageToolBig.png" alt="LT Icon"/>
		<b>LanguageTool</b><br/>
		freie Grammatik- und Stilpr&uuml;fung f&uuml;r LibreOffice und OpenOffice.org
	</a>
	<div id="piechart1div" style="float:left;width:510px;"></div>
	<div id="piechart2div" style="float:left;width:560px;"></div>
	<a href="http://shop.highsoft.com/highcharts.html" target="_blank" class="ad" id="ad3" style="background-color: rgba(200,200,200,.9);">
		<img src="by-nc.eu.png" alt="CC-by-nc"/>
		<b>Highcharts JS</b><br/>
		Highcharts JS kann f&uuml;r nicht kommerzielle Zwecke frei unter der CC-by-nc-Lizenz verwendet werden.
	</a>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	
	<script src="highcharts.src.js"></script>
	
	<script type="text/javascript">
	
	$(function () {
		var chart1, chart2, chart3, piechart1, piechart2;
		var lastUpdate='4. Jan 12 \u2013 2. Apr 12';
		$(document).ready(function() {
			chart1 = new Highcharts.Chart({
				chart: {
					renderTo: 'chart1div',
					zoomType: 'xy',
					alignTicks: false,
				},
				title: {
					text: 'house numbers'
				},
				subtitle: {
					text: 'missing data are linearly interpolated'
				},
				xAxis: {
					type: 'datetime',
					dateTimeLabelFormats: {
						day: '%e. %b %y',
						week: '%e. %b %y',
						month: '%e. %b %y',
						year: '%b'
					}
				},
				yAxis: [{ // Primary yAxis
					labels: {
						formatter: function() {
							return this.value;
						},
						style: {
							color: '#000000'
						}
					},
					title: {
						text: 'house numbers',
						style: {
							color: '#000000'
						}
					},
					max: 3000000,
					min: -3000000
				}, { // Secondary yAxis
					title: {
						text: 'difference',
						style: {
							color: '#888888'
						}
					},
					labels: {
						formatter: function() {
							return this.value;
						},
						style: {
							color: '#888888'
						}
					},
					opposite: true,
					gridLineWidth: 0,
					max: 7000,
					min: -7000
				}],
				legend: {
					enabled: false
				},
				tooltip: {
					formatter: function() {
					return Highcharts.dateFormat('%e. %b %y', this.x) +': '+ this.y;
					}
				},
				series: [{
					name: 'difference',
					color: '#888888',
					type: 'column',
					yAxis: 1,
					data: [
					<?php
						include("connect.php");
						$last=-1;
						$name="housenumbers";
						$entries=mysql_query("SELECT date, $name FROM stats") or die ("MySQL-Error: ".mysql_error());
						while($entry=mysql_fetch_assoc($entries)) {
							$current=$entry["$name"];
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							echo '[';
							echo "Date.UTC(".$date[0].",".($date[1]-1).",".$date[2]."),";
							if($last==-1) echo '0,'; else echo ($current-$last);
							echo '],';
							$last=$current;
						}
					?>
					]
				}, {
					name: 'housenumbers',
					color: '#000000',
					type: 'spline',
					data: [
					<?php
						$entries=mysql_query("SELECT date, $name FROM stats") or die ("MySQL-Error: ".mysql_error());
						while($entry=mysql_fetch_assoc($entries)) {
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							echo '[';
							echo "Date.UTC(".$date[0].",".($date[1]-1).",".$date[2]."),";
							echo $entry["$name"];
							echo '],';
						}
					?>
					]
				}]
			});
			chart2 = new Highcharts.Chart({
				chart: {
					renderTo: 'chart2div',
					zoomType: 'xy',
					alignTicks: false,
				},
				title: {
					text: 'dupes'
				},
				subtitle: {
					text: 'missing data are linearly interpolated'
				},
				xAxis: {
					type: 'datetime',
					dateTimeLabelFormats: {
						day: '%e. %b %y',
						week: '%e. %b %y',
						month: '%e. %b %y',
						year: '%b'
					}
				},
				yAxis: [{ // Primary yAxis
					labels: {
						formatter: function() {
							return this.value;
						},
						style: {
							color: '#000000'
						}
					},
					title: {
						text: 'dupes',
						style: {
							color: '#000000'
						}
					},
					max: 20000,
					min: -20000
				}, { // Secondary yAxis
					title: {
						text: 'difference',
						style: {
							color: '#888888'
						}
					},
					labels: {
						formatter: function() {
							return this.value;
						},
						style: {
							color: '#888888'
						}
					},
					opposite: true,
					max: 400,
					min: -400
				}],
				legend: {
					enabled: false
				},
				tooltip: {
					formatter: function() {
					return Highcharts.dateFormat('%e. %b %y', this.x) +': '+ this.y;
					}
				},
				series: [{
					name: 'difference',
					color: '#888888',
					type: 'column',
					yAxis: 1,
					data: [
					<?php
						$last=-1;
						$name="dupes";
						$entries=mysql_query("SELECT date, $name FROM stats") or die ("MySQL-Error: ".mysql_error());
						while($entry=mysql_fetch_assoc($entries)) {
							$current=$entry["$name"];
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							echo '[';
							echo "Date.UTC(".$date[0].",".($date[1]-1).",".$date[2]."),";
							if($last==-1) echo '0,'; else echo ($current-$last);
							echo '],';
							$last=$current;
						}
					?>
					]
				}, {
					name: 'dupes',
					color: '#000000',
					type: 'spline',
					data: [
					<?php
						$entries=mysql_query("SELECT date, $name FROM stats") or die ("MySQL-Error: ".mysql_error());
						while($entry=mysql_fetch_assoc($entries)) {
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							echo '[';
							echo "Date.UTC(".$date[0].",".($date[1]-1).",".$date[2]."),";
							echo $entry["$name"];
							echo '],';
						}
					?>
					]
				}]
			});
			chart3 = new Highcharts.Chart({
				chart: {
					renderTo: 'chart3div',
					zoomType: 'xy',
					alignTicks: false,
				},
				title: {
					text: 'problematic'
				},
				subtitle: {
					text: 'missing data are linearly interpolated'
				},
				xAxis: {
					type: 'datetime',
					dateTimeLabelFormats: {
						day: '%e. %b %y',
						week: '%e. %b %y',
						month: '%e. %b %y',
						year: '%b'
					}
				},
				yAxis: [{ // Primary yAxis
					labels: {
						formatter: function() {
							return this.value;
						},
						style: {
							color: '#000000'
						}
					},
					title: {
						text: 'problematic',
						style: {
							color: '#000000'
						}
					},
					max: 2200,
					min: -2200
				}, { // Secondary yAxis
					title: {
						text: 'difference',
						style: {
							color: '#888888'
						}
					},
					labels: {
						formatter: function() {
							return this.value;
						},
						style: {
							color: '#888888'
						}
					},
					opposite: true,
					gridLineWidth: 0,
					max: 120,
					min: -120
					
				}],
				legend: {
					enabled: false
				},
				tooltip: {
					formatter: function() {
					return Highcharts.dateFormat('%e. %b %y', this.x) +': '+ this.y;
					}
				},
				series: [{
					name: 'difference',
					color: '#888888',
					type: 'column',
					yAxis: 1,
					data: [
					<?php
						$last=-1;
						$name="problematic";
						$entries=mysql_query("SELECT date, $name FROM stats") or die ("MySQL-Error: ".mysql_error());
						while($entry=mysql_fetch_assoc($entries)) {
							$current=$entry["$name"];
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							echo '[';
							echo "Date.UTC(".$date[0].",".($date[1]-1).",".$date[2]."),";
							if($last==-1) echo '0,'; else echo ($current-$last);
							echo '],';
							$last=$current;
						}
					?>
					]
				}, {
					name: 'problematic',
					color: '#000000',
					type: 'spline',
					data: [
					<?php
						$entries=mysql_query("SELECT date, $name FROM stats") or die ("MySQL-Error: ".mysql_error());
						while($entry=mysql_fetch_assoc($entries)) {
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							echo '[';
							echo "Date.UTC(".$date[0].",".($date[1]-1).",".$date[2]."),";
							echo $entry["$name"];
							echo '],';
						}
					?>
					]
				}]
			});
			piechart1 = new Highcharts.Chart({
				chart: {
					renderTo: 'piechart1div',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'browsers'
				},
				subtitle: {
					text: lastUpdate
				},
				tooltip: {
					formatter: function() {
					return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
					}
				},
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: true,
							color: '#000000',
							connectorColor: '#000000',
							formatter: function() {
							return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
							}
						}
					}
				},
				series: [{
					type: 'pie',
					name: 'browsers',
					data: [
						{
							name: 'Firefox',
							y: 611,
							color: 'tomato',
						},
						{
							name: 'Chrome',
							y: 94,
							color: 'forestgreen',
						},
						{
							name: 'Opera',
							y: 86,
							color: 'maroon',
						},
						{
							name: 'Konqueror',
							y: 34,
							color: 'royalblue',
						},
						{
							name: 'Bot',
							y: 19,
							color: 'silver',
						},
						{
							name: 'IE',
							y: 22,
							color: 'dodgerblue',
						},
						{
							name: 'Mobile',
							y: 11,
							color: 'yellowgreen',
						},
						{
							name: 'Safari',
							y: 7,
							color: 'lightgray',
						},
					]
				}]
			});
			piechart2 = new Highcharts.Chart({
				chart: {
					renderTo: 'piechart2div',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'os'
				},
				subtitle: {
					text: lastUpdate
				},
				tooltip: {
					formatter: function() {
					return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
					}
				},
				plotOptions: {
					pie: {
						allowPointSelect: true,
						cursor: 'pointer',
						dataLabels: {
							enabled: true,
							color: '#000000',
							connectorColor: '#000000',
							formatter: function() {
							return '<b>'+ this.point.name +'</b>: '+ Math.round(this.percentage) +' %';
							}
						}
					}
				},
				series: [{
					type: 'pie',
					name: 'os',
					data: [
						{
							name: 'Windows',
							y: 415,
							color: 'royalblue',
						},
						{
							name: 'Linux',
							y: 384,
							color: 'goldenrod',
						},
						{
							name: 'MacOSX',
							y: 56,
							color: 'dimgray',
						},
						{
							name: 'Mobile',
							y: 11,
							color: 'yellowgreen',
						},
						{
							name: 'Bot',
							y: 19,
							color: 'silver',
						},
					]
				}]
			});
		});
	});
	
	</script>
	
</body>
</html>