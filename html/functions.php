<?php
	//! @param uid: osm user name
	function generateUserLink($uid) {
		return "<a src=\"_blank\" href=\"http://openstreetmap.org/user/$uid/\">$uid</a>";
	}
	
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
?>