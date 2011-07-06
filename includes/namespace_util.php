<?php 
	//Given a namespace string and a local constant as string, wrap with namespace
	function wrap_constant_name($ns, $loc_name){
		 return $ns . "_$loc_name";		
	}