<?php 

	const plugin_name = "embpicamoto";
	
	//Given a namespace string and a local constant as string, wrap with namespace
	function wrap_constant_name($ns, $loc_name){
		 return $ns . "_$loc_name";		
	}
	
	//Append the plugin name to a string given
	function append_plugin_name($str, $glue = ""){
		return plugin_name . $glue . $str;
	}