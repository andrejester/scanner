<?php
class DBGrid{
  var $AddColumn = array() ;
  var $Array = array() ;
	var $Col = array() ;
  function Init(){
    $this->AddColumn = array() ;
    $this->Array = array() ;
    $this->Col = array() ;    
		
		$this->CopyProper() ;
  }

	function __set($proper,$value){
		dbg::${$proper} = $value;
	}

	function __get($proper){
		return dbg::${$proper} ;
	}

	function __call($func,$args){
		return call_user_func_array("dbg::" . $func,$args) ;
	}

	private function CopyProper(){
		dbg::$AddColumn =  $this->AddColumn ;
    dbg::$Array = $this->Array ;
    dbg::$Col = $this->Col ;
	}

  function dataSource($dbData){
		$this->Array = dbg::dataSource($dbData) ;
  }

  function SQL($cSQL){
    $this->Array = dbg::SQL($cSQL) ;
  }

  function dataBind(){
		$this->CopyProper() ;
		dbg::dataBind() ;
    $this->Init() ; 
  }
}
?>