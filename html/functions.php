<?php
	//! @returns a link to a osm user page
	//! @param uid: osm user name
	function generateUserLink($uid) {
		return "<a src=\"_blank\" href=\"http://openstreetmap.org/user/$uid/\">$uid</a>";
	}
	
	//! @returns a comma seperated list of links to osm objects
	//! @param objects: comma seperated list of objects (ways and nodes), format $type[0,1]-$id
	function generateObjectLinks($objects) {
		$links="";
		$append="";
		
		if(strlen($objects)>250)
			$append="&hellip;";
		
		$objects=explode(",",$objects);
		
		foreach ($objects as $object) {
			$object=explode("-",$object);
			$type=($object[0]==0?"node":"way");
			$id=$object[1];
			
			$links.="<a src=\"_blank\" href=\"http://openstreetmap.org/browse/$type/$id\">$type $id</a>, ";
		}
		
		return substr($links,0,-2).$append;
	}
	
	//! @returns all the links for an object which are displayed in the bubble (osm link, josm link, etc.)
	//! @param id
	//! @param type way or node
	//! @param lat
	//! @param lon
	function generateObjectLinkForBubble($id, $type, $lat, $lon) {
		return '<a target="_blank" title="Details bei OSM anzeigen" href="http://www.openstreetmap.org/browse/'.$type.'/'.$id.'">'.$id.'</a> '
		      .'<a target="josmframe" title="in JOSM bearbeiten" href="http://localhost:8111/load_and_zoom?left='.($lon-0.001).'&right='.($lon+0.001).'&top='.($lat+0.001).'&bottom='.($lat-0.001).'&select='.$type.$id.'"><img src="josm.png" alt="JOSM"/></a>&thinsp;'
		      .'<a target="_blank" title="in Potlatch 2 bearbeiten" href="http://www.openstreetmap.org/edit?zoom=18&'.$type.'='.$id.'&editor=potlatch2"><img src="potlatch.png" alt="Potlatch"/></a>&thinsp;'
		      .'<a target="_blank" title="in iD bearbeiten" href="http://www.openstreetmap.org/edit?zoom=18&'.$type.'='.$id.'&editor=id">iD</a>';
	}
?>