<!DOCTYPE HTML>
<html>
<head>
	<title>housenumbervalidator &dash; statistic</title>
	<style type="text/css">
	</style>
</head>
<body>
	
	<!--[if lt IE 8]>
	<p style="color:red;font-size:20px;max-width:80%">Ihr Browser ist alt, unsicher und langsam!</p>
	<p style="font-size:20px;max-width:80%">Sie benutzen eine sehr alte Version des Internet Explorers, welche unsicher und langsam ist und nicht in der Lage ist, diese und andere Webseiten richtig darzustellen.<br/>
	Laden Sie sich <a href="http://www.microsoft.com/windows/internet-explorer/" target="_blank">die aktuelle Version des Internet Explorers</a> kostenlos herunter, benutzen Sie einen anderen kostenlosen Browser, z.B. <a href="http://www.mozilla.org/firefox/" target="_blank">Mozilla Firefox</a> oder <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a> oder installieren Sie ein anderes Betriebssystem, z.B. das freie <a href="http://ubuntuusers.de" target="_blank">Ubuntu</a>.</p>
	<![endif]-->
	
	<script type="text/javascript">
		document.write("<iframe style=\"display:none;\" id=\"counterframe\" src=\"../counter.php?id=hnrv_stat&ref="  + document.referrer + "\"></iframe>");
	</script>
	
	<canvas id="canvas" width="1250" height="800">
	Ihr Browser unterst&uuml;tzt kein HTML5 Canvas. Bitte aktualisieren Sie Ihren Browser, verwenden Sie einen anderen kostenlosen Browser, z.B. <a href="http://www.mozilla.org/firefox/" target="_blank">Mozilla Firefox</a> oder <a href="http://www.google.com/chrome/" target="_blank">Google Chrome</a>, oder installieren Sie ein anderes Betriebssystem, z.B. das freie <a href="http://ubuntuusers.de" target="_blank">Ubuntu</a>.</p>
	</canvas>
	</div>
	
	<script type="text/javascript">
	
	// parts taken from http://html5.litten.com/graphing-data-in-the-html5-canvas-element-part-i/
	
	var canvas;
	var ctx;
	var j;
	var x=0;
	var y=105;
	var WIDTH=400;
	var HEIGHT=400;
	var labels=["housenumbers", "dupes", "problematic"];
	var factors=["", .00004, .04, .01, 1, .08, .8];
	var colors=["", "black", "grey", "black", "grey", "black", "grey"];
	var values=[
		<?php
			include("connect.php");
			
			$last[0]=-1;
			$last[1]=-1;
			$last[2]=-1;
			
			$entries=mysql_query("SELECT * FROM stats") or die ("MySQL-Error: ".mysql_error());
			
			while($entry=mysql_fetch_assoc($entries)) {
				$current[0]=$entry['housenumbers'];
				$current[1]=$entry['dupes'];
				$current[2]=$entry['problematic'];
				echo '["';
				if($last[0]==-1) {
					echo $entry['date'].'",';
					echo $current[0].',';
					echo '0,';
					echo $current[1].',';
					echo '0,';
					echo $current[2].',';
					echo '0,';
				} else {
					echo $entry['date'].'",';
					echo $current[0].',';
					echo ($current[0]-$last[0]).',';
					echo $current[1].',';
					echo ($current[1]-$last[1]).',';
					echo $current[2].',';
					echo ($current[2]-$last[2]).',';
				}
				echo '],';
				$last[0]=$current[0];
				$last[1]=$current[1];
				$last[2]=$current[2];
			}
		?>
	];
	var ua=[
		["Firefox",611],
		["Chrome",94],
		["Konqueror",34],
		["Opera",86],
		["Bot",19],
		["IE",22],
		["Mobile",11],
		["Safari",7]
	];
	var uacolors=["tomato","forestgreen","royalblue","maroon","silver","dodgerblue","yellowgreen","lightgray"];
	var os=[
		["Windows",415],
		["Linux",384],
		["MacOSX",56],
		["Mobile",11],
		["Bot",19]
	];
	var oscolors=["royalblue","goldenrod","dimgray","yellowgreen","silver"];
	var lastUpdate="2012-04-02";
	
	var STEP=WIDTH/values.length;
	STEP*=.95;
	
	function drawaxes(i) {
		ctx.strokeStyle=colors[i*2+1];
		/* y axis along the left edge of the canvas*/
		ctx.beginPath();
		ctx.moveTo(WIDTH*i,0);
		ctx.lineTo(WIDTH*i,HEIGHT-i*50);
		ctx.stroke();
		/* 2nd y axis along the right edge of the canvas*/
		ctx.strokeStyle=colors[i*2+2];
		ctx.beginPath();
		ctx.moveTo(WIDTH*(i+1),0);
		ctx.lineTo(WIDTH*(i+1),HEIGHT);
		ctx.stroke();
		/* x axis along the middle of the canvas*/
		ctx.strokeStyle=colors[i*2+1];
		ctx.moveTo(WIDTH*i,HEIGHT/2);
		ctx.lineTo(WIDTH*(i+1),HEIGHT/2);
		ctx.stroke();
	}
	
	function addlabels(i) {
		ctx.font="10pt Arial";
		ctx.textBaseline="middle";
		/* y axis labels */
		ctx.fillStyle=colors[i*2+1];
		ctx.textAlign="left";
		ctx.fillText(labels[i], WIDTH*i+5, 15); 
		ctx.fillText("0", WIDTH*i+5, HEIGHT/2);
		ctx.fillText(HEIGHT/4/factors[i*2+1], WIDTH*i+5, HEIGHT/4);
		ctx.fillText(HEIGHT/2/factors[i*2+1], WIDTH*i+5, 5);
		ctx.fillText(-HEIGHT/4/factors[i*2+1], WIDTH*i+5, HEIGHT/4*3);
		ctx.fillText(-HEIGHT/2/factors[i*2+1], WIDTH*i+5, HEIGHT-5);
		/* 2nd y axis labels */
		ctx.fillStyle=colors[i*2+2];
		ctx.textAlign="right";
		ctx.fillText(labels[i]+" difference", WIDTH*(i+1)-5, 15); 
		ctx.fillText("0", WIDTH*(i+1)-5, HEIGHT/2);
		ctx.fillText(HEIGHT/4/factors[i*2+2], WIDTH*(i+1)-5, HEIGHT/4);
		ctx.fillText(HEIGHT/2/factors[i*2+2], WIDTH*(i+1)-5, 5);
		ctx.fillText(-HEIGHT/4/factors[i*2+2], WIDTH*(i+1)-5, HEIGHT/4*3);
		ctx.fillText(-HEIGHT/2/factors[i*2+2], WIDTH*(i+1)-5, HEIGHT-5);
		/* x axis labels */
		ctx.fillStyle=colors[i*2+1];
		ctx.textAlign="center";
		for(var j=1; j<values.length; j+=Math.floor(values.length/3)) {
			ctx.fillText(values[j][0], j*STEP+i*WIDTH, HEIGHT/2+8);
		}
	}
	
	function sum(a) {
		var c=0;
		for(var i=0; i<a.length; i++) {
			c+=a[i][1];
		}
		return c;
	}
	
	function clear() {
		ctx.clearRect(0, 0, WIDTH*3, HEIGHT*2);
	}
	
	function init() {
		canvas=document.getElementById("canvas");
		ctx=canvas.getContext("2d");
	}
	
	function plotdata(k) {
		var i=k*2+1
		ctx.strokeStyle=colors[i];
		ctx.beginPath();
		ctx.moveTo(0+WIDTH*k,HEIGHT/2-(values[0][i])*factors[i]);
		for(var j=1; j<values.length; j++) {
			ctx.lineTo(j*STEP+WIDTH*k,HEIGHT/2-(values[j][i])*factors[i]);
			ctx.stroke();
		}
		ctx.strokeStyle=colors[i+1];
		for(var j=1; j<values.length; j++) {
			ctx.rect(((j)*STEP+WIDTH*k)-STEP*.25, HEIGHT/2, STEP/2, -(values[j][i+1])*factors[i+1]);
			ctx.stroke();
		}
	}
	
	function piechart(a,c,x) {
		var lastEnd=(Math.PI/180)*-90;
		var mySum=sum(a);
		
		ctx.textAlign="left";
		for(var i=0; i<a.length; i++) {
			ctx.fillStyle=c[i];
			ctx.beginPath();
			ctx.moveTo(x,HEIGHT*1.5);
			ctx.arc(x,HEIGHT*1.5,150,lastEnd,lastEnd+
			(Math.PI*2*(a[i][1]/mySum)),false);
			ctx.lineTo(x,HEIGHT*1.5);
			ctx.fill();
			lastEnd+=Math.PI*2*(a[i][1]/mySum);
			ctx.fillText(a[i][0] + " " + Math.round(a[i][1]/mySum*100) + " %", x-250, HEIGHT+60+i*15); 
		}
		ctx.fillStyle="black";
		ctx.fillText(lastUpdate, 0, HEIGHT*2-60); 
	}
	
	function draw() {
		init();
		clear();
		for(var i=0; i<3; i++) {
			drawaxes(i);
			addlabels(i);
			plotdata(i);
		}
		
		piechart(ua, uacolors, WIDTH*.5+50);
		piechart(os, oscolors, WIDTH*1.5+50);
	}
	
	draw();
	</script>
	
	<div>
		<a href="http://gulp21.bplaced.net/osm/housenumbervalidator/">Zum housenumbervalidator</a>
	</div>
	<hr/>
	<div>
		<a href="http://gulp21.github.com/qeodart_de.html">QeoDart &dash; ein freies Geographie-Lernspiel</a> <br/>
		<a href="http://languagetool.org/de/">LanguageTool &dash; eine freie Stil- und Grammatikpr&uuml;fung f&uuml;r LibreOffice/OpenOffice.org</a>
	</div>
</body>
</html>