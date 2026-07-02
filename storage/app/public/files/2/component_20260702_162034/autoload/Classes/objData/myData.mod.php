<?php
  include 'df.php' ;

class myData { 
	function __call($func,$args){
		return call_user_func_array("objData::" . $func,$args) ;
	}
}