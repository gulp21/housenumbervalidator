<!DOCTYPE HTML>
<html style="font-family: 'Times New Roman', serif;">
<head>
	<meta charset="UTF-8" />
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
	<p style="font-size:20px;max-width:80%">Sie benutzen eine alte Version des Internet Explorers, die langsam ist und nicht in der Lage ist, diese und andere Webseiten richtig darzustellen.<br/>
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
		das freie Geographie-Lernspiel für Linux &amp; Windows
	</a>
	<div id="chart1div"></div>
	<div id="chart2div"></div>
	<div id="chart3div"></div>
	<a href="http://languagetool.org/de" target="_blank" class="ad" id="ad2" style="background-color: rgba(152,184,240,.9);">
		<img src="LanguageToolBig.png" alt="LT Icon"/>
		<b>LanguageTool</b><br/>
		freie Grammatik- und Stilprüfung für LibreOffice und OpenOffice.org
	</a>
	<div id="piechart1div" style="float:left;width:510px;"></div>
	<div id="piechart2div" style="float:left;width:560px;"></div>
	<div id="chart4div" style="clear:left;"></div>
	<a href="http://shop.highsoft.com/highcharts.html" target="_blank" class="ad" id="ad3" style="background-color: rgba(200,200,200,.9);">
		<img src="by-nc.eu.png" alt="CC-by-nc"/>
		<b>Highcharts JS</b><br/>
		Highcharts JS kann für nicht kommerzielle Zwecke frei unter der CC-by-nc-Lizenz verwendet werden.
	</a>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	
	<script src="highcharts.src.js"></script>
	
	<script type="text/javascript">
	
	$(function () {
		var chart1, chart2, chart3, piechart1, piechart2, chart4;
		function formatter(t) {
			annotation="";
			if(t.x==Date.UTC(2012,1,20)) annotation="<br/><b>Algorithmusänderung<b>";
			else if(t.x==Date.UTC(2012,1,21)) annotation="<br/><b>Algorithmusänderung<b>";
			else if(t.x==Date.UTC(2012,2,14)) annotation="<br/><b>xybot<b>";
			else if(t.x==Date.UTC(2012,2,24)) annotation="<br/><b>Algorithmusänderung<b>";
			else if(t.x==Date.UTC(2012,3,2)) annotation="<br/><b>Datenbank read-only<b>";
			else if(t.x==Date.UTC(2012,3,3)) annotation="<br/><b>Datenbank read-only<b>";
			else if(t.x==Date.UTC(2012,3,4)) annotation="<br/><b>Datenbank read-only<b>";
			else if(t.x==Date.UTC(2012,3,29)) annotation="<br/><b>Katasterimport Kreis Viersen<b>";
			else if(t.x==Date.UTC(2012,3,30)) annotation="<br/><b>Katasterimport Kreis Viersen<b>";
			else if(t.x==Date.UTC(2012,4,03)) annotation="<br/><b>Algorithmuskorrektur<b>";
			else if(t.x==Date.UTC(2012,5,08)) annotation="<br/><b>Algorithmusänderung<b>";
			else if(t.x==Date.UTC(2012,5,11)) annotation="<br/><b>Algorithmusänderung<b>";
			else if(t.x==Date.UTC(2012,5,13)) annotation="<br/><b>Algorithmuskorrektur<b>";
			else if(t.x==Date.UTC(2012,5,16)) annotation="<br/><b>erweiterte Duplikatsprüfung<b>";
			else if(t.x==Date.UTC(2012,5,17)) annotation="<br/><b>Algorithmusänderung<b>";
			else if(t.x==Date.UTC(2012,5,23)) annotation="<br/><b>erweiterte Duplikatsprüfung<b>";
			else if(t.x==Date.UTC(2012,5,24)) annotation="<br/><b>Algorithmusänderung<b>";
			else if(t.x==Date.UTC(2012,6,18)) annotation="<br/><b>OSMF Redaction Account<b>";
			else if(t.x==Date.UTC(2012,6,19)) annotation="<br/><b>OSMF Redaction Account<b>";
			else if(t.x==Date.UTC(2012,6,20)) annotation="<br/><b>OSMF Redaction Account<b>";
			return Highcharts.dateFormat('%e. %b %y', t.x) + ': ' + t.y + annotation;
		};
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
						return formatter(this);
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
						$entries=mysql_query("SELECT date, $name FROM stats ORDER BY date") or die ("MySQL-Error: ".mysql_error());
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
						$entries=mysql_query("SELECT date, $name FROM stats ORDER BY date") or die ("MySQL-Error: ".mysql_error());
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
						return formatter(this);
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
						$entries=mysql_query("SELECT date, $name FROM stats ORDER BY date") or die ("MySQL-Error: ".mysql_error());
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
						$entries=mysql_query("SELECT date, $name FROM stats ORDER BY date") or die ("MySQL-Error: ".mysql_error());
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
						return formatter(this);
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
						$entries=mysql_query("SELECT date, $name FROM stats ORDER BY date") or die ("MySQL-Error: ".mysql_error());
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
						$entries=mysql_query("SELECT date, $name FROM stats ORDER BY date") or die ("MySQL-Error: ".mysql_error());
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
					text: '4. Jan 12 \u2013 today'
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
							y:
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Firefox%" or ua like "%Namoroka%")');
							echo mysql_num_rows($r);
							?>,
							color: 'tomato',
						},
						{
							name: 'Chrome',
							y:
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Chrome%")');
							echo mysql_num_rows($r);
							?>,
							color: 'forestgreen',
						},
						{
							name: 'Opera',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Opera%")');
							echo mysql_num_rows($r);
							?>,
							color: 'maroon',
						},
						{
							name: 'IE',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%MSIE%")');
							echo mysql_num_rows($r);
							?>,
							color: 'dodgerblue',
						},
						{
							name: 'Safari',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Safari%") and not(ua like "%Chrome%") and not(ua like "%Mobile%") and not(ua like "%Googlebot-Mobile%")');
							echo mysql_num_rows($r);
							?>,
							color: 'lightgray',
						},
						{
							name: 'Konqueror',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Konqu%")');
							echo mysql_num_rows($r);
							?>,
							color: 'royalblue',
						},
						{
							name: 'Mobile',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Mobile%")');
							echo mysql_num_rows($r);
							?>,
							color: 'yellowgreen',
						},
						{
							name: 'Bot',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%http%")');
							echo mysql_num_rows($r);
							?>,
							color: 'silver',
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
					text: '4. Jan 12 \u2013 today'
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
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Windows%")');
							echo mysql_num_rows($r);
							?>,
							color: 'royalblue',
						},
						{
							name: 'Linux',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Linux%") and not(ua like "%Android%")');
							echo mysql_num_rows($r);
							?>,
							color: 'goldenrod',
						},
						{
							name: 'MacOSX',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Macin%")');
							echo mysql_num_rows($r);
							?>,
							color: 'dimgray',
						},
						{
							name: 'Mobile',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Mobile%")');
							echo mysql_num_rows($r);
							?>,
							color: 'yellowgreen',
						},
						{
							name: 'Bot',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%http%")');
							echo mysql_num_rows($r);
							?>,
							color: 'silver',
						},
					]
				}]
			});
			chart4 = new Highcharts.Chart({
				chart: {
					renderTo: 'chart4div',
					zoomType: 'xy',
					alignTicks: false,
				},
				title: {
					text: 'unique visits per day'
				},
				subtitle: {
					text:
					<?php
					$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv"');
					echo mysql_num_rows($r);
					?>
					+ ' total'
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
						text: 'visits',
						style: {
							color: '#000000'
						}
					},
					max: 50,
					min: 0
				}],
				legend: {
					enabled: false
				},
				tooltip: {
					formatter: function() {
						return 'week of ' + Highcharts.dateFormat('%e. %b %y', this.x) + ': ' + this.y;
					},
				},
				series: [{
					name: 'visits',
					color: '#000000',
					type: 'spline',
					yAxis: 0,
					data: [
					<?php
					function dateDiff($lhd, $rhd) {
						return strtotime($rhd)-strtotime($lhd);
					}
						
					for($i=0; ; $i+=7) {
						$start = 0+$i;
						$end = 7+$i;
						$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and time between date_add("12/01/9", interval '.$start.' day) and date_add("12/01/10", interval '.$end.' day)');
						if(mysql_num_rows($r)==0)
							break;
						
						$divisor = 7;
						$diff=dateDiff(
								date_format(
									(date_add(new DateTime("2012-01-10"), date_interval_create_from_date_string($end.' days'))), 'Y-m-d H:i'
								),
								date_format(date_create(),'Y-m-d H:i')
							);
						if($diff<0) {
							$divisor = ((7*60*60*24+$diff)/(60*60*24));
						}
						
						$ave=round(mysql_num_rows($r)/$divisor, 1);
						
						echo "[Date.UTC(2012,0,9+".$i."),".$ave."],\n";
					}
					?>
					]
				}]
			});
		});
	});
	
	</script>
	
</body>
</html>
