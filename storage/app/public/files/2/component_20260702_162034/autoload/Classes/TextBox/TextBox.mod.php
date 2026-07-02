<?php
class TextBox{
	function __set($proper,$value){
		txt::${$proper} = $value;
	}

	function __get($proper){
		return txt::${$proper} ;
	}

	function __call($func,$args){
		return call_user_func_array("txt::" . $func,$args) ;
	}
}