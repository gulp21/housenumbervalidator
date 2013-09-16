<!DOCTYPE HTML>
<html style="font-family: 'Times New Roman', serif;">
<head>
	<meta charset="UTF-8"/>
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
	<div id="chartHouseNumbersDiv"></div>
	<div id="chartDupesDiv"></div>
	<div id="chartProblemsDiv"></div>
	<a href="http://languagetool.org/de" target="_blank" class="ad" id="ad2" style="background-color: rgba(152,184,240,.9);">
		<img src="LanguageToolBig.png" alt="LT Icon"/>
		<b>LanguageTool</b><br/>
		freie Grammatik- und Stilprüfung für LibreOffice, OpenOffice.org, Mozilla Thunderbird, Firefox, vim uvm.
	</a>
	<div id="pieChartBrowsersDiv" style="float:left;width:510px;"></div>
	<div id="pieChartOsDiv" style="float:left;width:570px;"></div>
	<div id="chartHitsDiv" style="clear:left;"></div>
	<div id="chartTopReferresDiv"></div>
	<a href="http://shop.highsoft.com/highcharts.html" target="_blank" class="ad" id="ad3" style="background-color: rgba(200,200,200,.9);">
		<img src="by-nc.eu.png" alt="CC-by-nc"/>
		<b>Highcharts JS</b><br/>
		Highcharts JS kann für nicht kommerzielle Zwecke frei unter der CC-by-nc-Lizenz verwendet werden.
	</a>
	
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	
	<script src="highcharts.src.js"></script>
	
	<?php
	function plot_diff($previous_date,$current_date,$date,$current,$last) {
		if(date_diff($previous_date,$current_date)->d>1) { // interpolate missing values
			echo " /*interpolateStart*/ ";
			$diff=date_diff($previous_date,$current_date)->d;
			for($i=0; $i<$diff; $i++) {
				echo '[';
				echo "Date.UTC(".$date[0].",".($date[1]-1).",".($date[2]+$i-$diff+1)."),";
				echo round(($current-$last)/$diff);
				echo '],';
			}
			echo " /*interpolateEnd*/ ";
		} else if(date_diff($previous_date,$current_date)->d<1) {
			echo " /*sthIsWrong*/ ";
		} else {
			echo '[';
			echo "Date.UTC(".$date[0].",".($date[1]-1).",".$date[2]."),";
			if($last==-1) echo '0,'; else echo ($current-$last);
			echo '],';
		}
	}
	?>
	
	<script type="text/javascript">
	
	$(function () {
		var chartHouseNumbers, chartDupes, chartProblems, pieChartBrowsers, pieChartOs, chartHits, chartTopReferres;
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
			else if(t.x==Date.UTC(2012,7,29)) annotation="<br/><b>xybot<b>";
			else if(t.x==Date.UTC(2012,9,10)) annotation="<br/><b>Algorithmusänderung<b>";
			else if(t.x==Date.UTC(2013,2,1)) annotation="<br/><b>Algorithmusänderung<b>";
			return Highcharts.dateFormat('%e. %b %y', t.x) + ': ' + t.y + annotation;
		};
		$(document).ready(function() {
			chartHouseNumbers = new Highcharts.Chart({
				chart: {
					renderTo: 'chartHouseNumbersDiv',
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
						include("./connect.php");
						$last=-1;
						$name="housenumbers";
						$entries=mysql_query("SELECT date, $name FROM stats ORDER BY date") or die ("MySQL-Error: ".mysql_error());
						$previous_date=new DateTime('2012-01-13');
						while($entry=mysql_fetch_assoc($entries)) {
							$current=$entry["$name"];
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							$current_date=new DateTime("$date[0]-$date[1]-$date[2]");
							plot_diff($previous_date,$current_date,$date,$current,$last);
							$last=$current;
							$previous_date=$current_date;
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
			chartDupes = new Highcharts.Chart({
				chart: {
					renderTo: 'chartDupesDiv',
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
					max: 22000,
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
						$previous_date=new DateTime('2012-01-13');
						while($entry=mysql_fetch_assoc($entries)) {
							$current=$entry["$name"];
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							$current_date=new DateTime("$date[0]-$date[1]-$date[2]");
							plot_diff($previous_date,$current_date,$date,$current,$last);
							$last=$current;
							$previous_date=$current_date;
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
			chartProblems = new Highcharts.Chart({
				chart: {
					renderTo: 'chartProblemsDiv',
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
						$previous_date=new DateTime('2012-01-13');
						while($entry=mysql_fetch_assoc($entries)) {
							$current=$entry["$name"];
							$date=$entry['date'];
							$date=explode("-",$entry['date']);
							$current_date=new DateTime("$date[0]-$date[1]-$date[2]");
							plot_diff($previous_date,$current_date,$date,$current,$last);
							$last=$current;
							$previous_date=$current_date;
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
			pieChartBrowsers = new Highcharts.Chart({
				chart: {
					renderTo: 'pieChartBrowsersDiv',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false
				},
				title: {
					text: 'browsers'
				},
				subtitle: {
					text: 'Jan 4 2012 \u2013 today'
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
							name: 'IE',
							y: 
							<?php
							$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%MSIE%")');
							echo mysql_num_rows($r);
							?>,
							color: 'dodgerblue',
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
						//Epiphany
					]
				}]
			});
			
			<?php
			$win_all=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Windows%")'));
			$win_xp=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Windows NT 5.1%")'));
			$win_vista=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Windows NT 6.0%")'));
			$win_7=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Windows NT 6.1%")'));
			$win_8=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Windows NT 6.2%")'));
			$win_other=$win_all-$win_xp-$win_vista-$win_7-$win_8;
			$linux_all=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Linux%") and not(ua like "%Android%")'));
			$macos_all=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Macin%")'));
			$mobile_all=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%Mobile%")'));
			$bot_all=mysql_num_rows(mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and (ua like "%http%")'));
			?>
			
			// parts taken from highcharts.com/tree/master/samples/highcharts/demo/pie-donut/
			categories = ['Windows', 'Linux', 'MacOSX', 'Mobile', 'Bot'],
			data = [{
				y: <?php echo $win_all;?>,
				color: 'royalblue',
				drilldown: {
					name: 'Windows versions',
					categories: ['Windows XP', 'Windows Vista', 'Windows 7', 'Windows 8', 'Windows other'],
					data: [<?php echo "$win_xp, $win_vista, $win_7, $win_8, $win_other";?>],
					color: ['rgb(65,110,225)', 'rgb(65,125,225)', 'rgb(65,140,225)', 'rgb(65,155,225)', 'rgb(65,170,255)']
				}
				}, {
				y: <?php echo $linux_all;?>,
				color: 'goldenrod',
				drilldown: {
					name: 'Linux',
					categories: ['Linux'],
					data: [<?php echo $linux_all;?>],
					color: ['goldenrod']
				}
				}, {
				y: <?php echo $macos_all;?>,
				color: 'dimgray',
				drilldown: {
					name: 'MacOSX',
					categories: ['MacOSX'],
					data: [<?php echo $macos_all;?>],
					color: ['dimgray']
				}
				}, {
				y: <?php echo $mobile_all;?>,
				color: 'yellowgreen',
				drilldown: {
					name: 'Mobile',
					categories: ['Mobile'],
					data: [<?php echo $mobile_all;?>],
					color: ['yellowgreen']
				}
				}, {
				y: <?php echo $bot_all;?>,
				color: 'silver',
				drilldown: {
					name: 'Bot',
					categories: ['Bot'],
					data: [<?php echo $bot_all;?>],
					color: ['silver']
				}
				}];
			
			// Build the data arrays
			var osData = [];
			var osVersionsData = [];
			for (var i = 0; i < data.length; i++) {
				// add browser data
				osData.push({
					name: categories[i],
					y: data[i].y,
					color: data[i].color
				});
				// add version data
				for (var j = 0; j < data[i].drilldown.data.length; j++) {
					osVersionsData.push({
						name: data[i].drilldown.categories[j],
						y: data[i].drilldown.data[j],
						color: data[i].drilldown.color[j]
					});
				}
			}
			
			pieChartOs = new Highcharts.Chart({
				chart: {
					renderTo: 'pieChartOsDiv',
					plotBackgroundColor: null,
					plotBorderWidth: null,
					plotShadow: false,
					type: 'pie'
				},
				title: {
					text: 'os'
				},
				subtitle: {
					text: 'Jan 4 2012 \u2013 today'
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
					name: 'OS',
					data: osData,
					size: '60%',
					dataLabels: {
						enabled: false
					}
				}, {
					name: 'Versions',
					data: osVersionsData,
					innerSize: '60%'
				}]
			});
			chartHits = new Highcharts.Chart({
				chart: {
					renderTo: 'chartHitsDiv',
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
					max: 70,
					min: 0,
					endOnTick: false
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
					for($i=0; ; $i+=7) {
						$start=0+$i;
						$end=7+$i;
						$r=mysql_query('SELECT DISTINCT ip,ua,time FROM `hits` WHERE id="hnrv" and time between date_add("12/01/9", interval '.$start.' day) and date_add("12/01/10", interval '.$end.' day)');
						if(mysql_num_rows($r)==0)
							break;
						
						$divisor = 7;
						$date_end=date_add(new DateTime("2012/01/09"), new DateInterval("P".$end."D"));
						$date_now=new DateTime("now");
						if($date_end>$date_now) {
							$divisor=7-((date_diff($date_end,$date_now)->d)
							         +(date_diff($date_end,$date_now)->h)/24);
							echo "/*".mysql_num_rows($r)." / $divisor "
							     .$date_end->format('Y-m-d H:i:s')." "
							     .$date_now->format('Y-m-d H:i:s')." "
							     .date_diff($date_end,$date_now)->d."*/";
						}
						
						$ave=round(mysql_num_rows($r)/$divisor, 1);
						
						echo "[Date.UTC(2012,0,9+".$i."),".$ave."],\n";
					}
					?>
					]
				}]
			});
			
			<?php
			$none=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "NONE%"'));
			$dnt=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "DNT%"'));
			$forum=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "%forum.openstreetmap.org%"'));
			$wiki=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "%wiki.openstreetmap.org%"'));
			$browse=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "%openstreetmap.org/browse%"'));
			$other_osm=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "%openstreetmap%"'))-$forum-$wiki-$browse;
			$bplaced=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "%gulp21.bplaced.net%" AND referrer NOT LIKE "%gulp21.bplaced.net/osm/housenumbervalidator%"'));
			$github=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "%gulp21.github.com%"'));
			$search=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE referrer LIKE "%.google.%"'));
			$other=mysql_num_rows(mysql_query('SELECT referrer FROM (SELECT * FROM `hits` WHERE id="hnrv" GROUP BY ip,time,ua) AS tmp WHERE id="hnrv" AND time>="2012-01-21 12:00:00" AND NOT(referrer LIKE "NONE%" OR referrer LIKE "DNT%" OR referrer LIKE "%openstreetmap%" OR referrer LIKE "%gulp21.bplaced.net%" OR referrer LIKE "%gulp21.github.com%" OR referrer LIKE "%.google.%" OR referrer LIKE "NULL%" OR referrer="\n")'));
			?>
			chartTopReferres = new Highcharts.Chart({
				chart: {
					renderTo: 'chartTopReferresDiv',
					type: 'bar'
				},
				title: {
					text: 'Top Referrers'
				},
				subtitle: {
					text: 'Jan 21 2012 \u2013 today'
				},
				xAxis: {
					categories: ['None', 'Do Not Track', 'openstreetmap.org/browse', 'wiki.openstreetmap.org', 'forum.openstreetmap.org', 'other openstreetmap', 'Search Engine', 'gulp21.github.com', 'gulp21.bplaced.net', 'Other'],
					title: {
					text: null
					}
				},
				yAxis: {
					title: {
					text: null
					},
					min: 0
				},
				plotOptions: {
					bar: {
					dataLabels: {
						enabled: true
					}
					}
				},
				tooltip: {
					formatter: function() {
					return ''+
						'<b>'+this.x+':</b> '+this.y;
					}
				},
				legend: {
					enabled: false
				},
				series: [{
					data: [<?php echo "$none, $dnt, $browse, $wiki, $forum, $other_osm, $search, $github, $bplaced, $other"; ?>]
				}]
			});
		});
	});
	
	</script>
	
</body>
</html>
