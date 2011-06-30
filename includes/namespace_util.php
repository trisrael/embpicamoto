<?php 
	//Given a namespace string and a local constant as string, wrap with namespace
	function wrap_namespace($ns, $loc_name){
		 return "\\" . $ns . "\\$loc_name";			
	}
	
