/*
housenumbervalidator Copyright (C) 2012 Markus Brenneis
This program comes with ABSOLUTELY NO WARRANTY.
This is free software, and you are welcome to redistribute it under certain conditions.
See https://github.com/gulp21/housenumbervalidator/blob/master/COPYING for details. */

if(window.innerWidth<=500 || window.innerHeight<=510) {
	document.getElementById("sidebar").className="hiddenBar";
}

// parts taken from openlayers.org/dev/examples

map = new OpenLayers.Map("mapdiv",
{
	controls: [
		new OpenLayers.Control.Navigation({documentDrag: true/*, dragPanOptions: {enableKinetic: true}*/}),
		new OpenLayers.Control.PanZoomBar(),
		new OpenLayers.Control.LayerSwitcher({'ascending':true}),
		new OpenLayers.Control.Permalink(),
		new OpenLayers.Control.Attribution()
	],
	projection: new OpenLayers.Projection("EPSG:900913"),
	displayProjection: new OpenLayers.Projection("EPSG:4326")
});

var mapnikMap = new OpenLayers.Layer.OSM.Mapnik("Mapnik",
{
	transitionEffect: 'resize',
	attribution: '&copy;&nbsp;<a href="http://osm.org" target="_blank">OpenStreetMap</a> contributors, <a href="http://www.openstreetmap.org/copyright" target="_blank">Data ODbl / Map CC-by-sa</a>'
});
map.addLayer(mapnikMap);

var mapquestMap = new OpenLayers.Layer.OSM("MapQuest", "http://otile1.mqcdn.com/tiles/1.0.0/osm/${z}/${x}/${y}.png", 
{
	transitionEffect: 'resize',
	numZoomLevels: 19,
	attribution: 'Tiles Courtesy of <a href="http://www.mapquest.com/" target="_blank">MapQuest</a> <img src="http://developer.mapquest.com/content/osm/mq_logo.png">, Data &copy;&nbsp;<a href="http://osm.org" target="_blank">OpenStreetMap</a> contributors, <a href="http://www.openstreetmap.org/copyright" target="_blank">ODbl</a>'
});
map.addLayer(mapquestMap);

var dupes_near = new OpenLayers.Layer.Vector("Duplikate (Sehr Nah)", {
	projection: map.displayProjection,
	strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1, ratio: 0.9})],
	protocol: new OpenLayers.Protocol.HTTP({
		url: "./get_dupes.php?dupe_type=0",
		format: new OpenLayers.Format.Text()
	})
});
map.addLayer(dupes_near);

var dupes_exact = new OpenLayers.Layer.Vector("Duplikate (Exakt)", {
	projection: map.displayProjection,
	strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1, ratio: 0.9})],
	protocol: new OpenLayers.Protocol.HTTP({
		url: "./get_dupes.php?dupe_type=1",
		format: new OpenLayers.Format.Text()
	})
});
map.addLayer(dupes_exact);

var dupes_similar = new OpenLayers.Layer.Vector("Duplikate (Ähnlich)", {
	projection: map.displayProjection,
	strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1, ratio: 0.9})],
	protocol: new OpenLayers.Protocol.HTTP({
		url: "./get_dupes.php?dupe_type=2",
		format: new OpenLayers.Format.Text()
	})
});
map.addLayer(dupes_similar);

var prob_easy = new OpenLayers.Layer.Vector("Problematisch (Einfach zu beheben)", {
	projection: map.displayProjection,
	strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1, ratio: 0.9})],
	protocol: new OpenLayers.Protocol.HTTP({
		url: "./get_problematic.php?prob_type=0",
		format: new OpenLayers.Format.Text()
	})
});
map.addLayer(prob_easy);

var prob_complicated = new OpenLayers.Layer.Vector("Problematisch (Kompliziert/Botaufgabe)", {
	projection: map.displayProjection,
	strategies: [new OpenLayers.Strategy.BBOX({resFactor: 1, ratio: 0.9})],
	protocol: new OpenLayers.Protocol.HTTP({
		url: "./get_problematic.php?prob_type=1",
		format: new OpenLayers.Format.Text()
	})
});
map.addLayer(prob_complicated);

// Interaction; not needed for initial display.
selectControl = new OpenLayers.Control.SelectFeature([dupes_near,dupes_exact,dupes_similar,prob_easy,prob_complicated]);
map.addControl(selectControl);
selectControl.activate();
dupes_near.events.on({
	'featureselected': onFeatureSelect,
	'featureunselected': onFeatureUnselect
});
dupes_exact.events.on({
	'featureselected': onFeatureSelect,
	'featureunselected': onFeatureUnselect
});
dupes_similar.events.on({
	'featureselected': onFeatureSelect,
	'featureunselected': onFeatureUnselect
});
prob_easy.events.on({
	'featureselected': onFeatureSelect,
	'featureunselected': onFeatureUnselect
});
prob_complicated.events.on({
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

function updateMap() {
	map.updateSize();
	map.setCenter(map.getCenter(), map.getZoom());
}

function toggleSidebar() {
	if(document.getElementById('sidebar').className=="visibleBar") {
		document.getElementById('sidebar').className="hiddenBar";
		document.getElementById('toggleSidebar').textContent="einblenden";
	} else {
		document.getElementById('sidebar').className="visibleBar";
		document.getElementById('toggleSidebar').textContent="ausblenden";
	}
	// this is needed because the map jumps when moving after hiding the sidebar (considering animation time)
// 	setTimeout('updateMap()', 600); // TODO not working well with animation
}

function hideMiddleBar() {
	document.getElementById('middleBar').className="hiddenBar";
}

function showMiddleBar() {
	document.getElementById('middleBar').className="visibleBar";
}

function showReport() {
	if(document.getElementById('reportdiv').className=='visibleContents'
	   && document.getElementById('middleBar').className=='visibleBar') {
		hideMiddleBar();
		return;
	}
	showMiddleBar();
	document.getElementById('reportdiv').className='visibleContents';
	document.getElementById('oneperdaydiv').className='hiddenContents';
	document.getElementById('areastatdiv').className='hiddenContents';
	document.getElementById('listdiv').className='hiddenContents';
}

function showOnePerDay() {
	if(document.getElementById('oneperdaydiv').className=='visibleContents'
	   && document.getElementById('middleBar').className=='visibleBar') {
		hideMiddleBar();
		return;
	}
	showMiddleBar();
	document.getElementById('reportdiv').className='hiddenContents';
	document.getElementById('oneperdaydiv').className='visibleContents';
	document.getElementById('areastatdiv').className='hiddenContents';
	document.getElementById('listdiv').className='hiddenContents';
}

function showAreaStat() {
	if(document.getElementById('areastatdiv').className=='visibleContents'
	   && document.getElementById('middleBar').className=='visibleBar') {
		hideMiddleBar();
		return;
	}
	showMiddleBar();
	document.getElementById('reportdiv').className='hiddenContents';
	document.getElementById('oneperdaydiv').className='hiddenContents';
	document.getElementById('areastatdiv').className='visibleContents';
	document.getElementById('listdiv').className='hiddenContents';
	bbox=map.getExtent().transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326")).toBBOX();
	document.getElementById('areastatframe').src="http://gulp21.bplaced.net/osm/housenumbervalidator/get_areastat.php?bbox="+bbox;
}

function showAsList() {
	if(document.getElementById('listdiv').className=='visibleContents'
	   && document.getElementById('middleBar').className=='visibleBar') {
		hideMiddleBar();
		return;
	}
	showMiddleBar();
	document.getElementById('reportdiv').className='hiddenContents';
	document.getElementById('oneperdaydiv').className='hiddenContents';
	document.getElementById('areastatdiv').className='hiddenContents';
	document.getElementById('listdiv').className='visibleContents';
	bbox=map.getExtent().transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326")).toBBOX();
	document.getElementById('listframe').src="http://gulp21.bplaced.net/osm/housenumbervalidator/get_dupes.php?simplelist=1&bbox="+bbox;
}

function openOsmi() {
	var position = map.getCenter().transform(map.getProjectionObject(), new OpenLayers.Projection("EPSG:4326"));
	var osmiWindow = window.open("http://tools.geofabrik.de/osmi/?view=addresses&lon="+position.lon+"&lat="+position.lat+"&zoom="+map.getZoom()+"&overlays=postal_code,no_addr_street,street_not_found,interpolation,interpolation_errors,connection_lines,nearest_points,nearest_roads").focus();
}

function refreshLayerAfterCorrectedClicked(id, type, layer) {
	if(layer=="prob_easy")
		prob_easy.refresh();
	else if(layer=="prob_complicated")
		prob_complicated.refresh();
	else if(layer=="dupes_near")
		dupes_near.refresh();
	else if(layer=="dupes_exact")
		dupes_exact.refresh();
	else if(layer=="dupes_similar")
		dupes_similar.refresh();
	else
		alert("unknown layer " + layer + " (" + id + " " + type + ")");
}

function markAsCorrectedClicked(id, type, layer) {
	document.getElementsByName('id')[0].value=id;
	document.getElementsByName('way_u')[0].checked=type;
	document.getElementsByName('way')[0].value=type;
	map.removePopup(map.popups[0]);
	
	window.setTimeout("refreshLayerAfterCorrectedClicked('"+id+"', '"+type+"', '"+layer+"')", 500);
}
/* from OpenLinkMap
function requestApi(file, query, handler)
{
	if (typeof handler == 'undefined')
		return OpenLayers.Request.GET({url: root+'api/'+file+'.php?'+query, async: false});
	else
		return OpenLayers.Request.GET({url: root+'api/'+file+'.php?'+query, async: true, success: handler});
}

var handler = function(request)
	{
		var content = request.responseText;
		if (content != "NULL")
		{
			detailsbar.innerHTML = content;
			detailsbar.innerHTML += "<div class='loadingMoreInfo'>"+loading+"</div>";
			fullscreen.init();
			//panorama.init();
		}
		else
		{
			detailsbar.innerHTML = "";
			detailsbar.className = "infoBarOut";
			gEBI('sideBar').className = "sideBarOut";
		}
	}
requestApi("extdetails", "id="+id+"&type="+type+"&format=text&offset="+offset+"&lang="+params['lang']+"&lat="+lat+"&lon="+lon, handler);
}*/
