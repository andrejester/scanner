<?php
/*
Berisi Script Controller dengan method yang bisa kita definisikan sendiri dengan syarat 
1. Semua Method hanya bisa di akses dengan a.ajax atau restfull dengan definisi Header khusus
2. Method yang bisa di akses dari URL public adalah yand di definisikan di Routes sebagai PublicMethod
*/
class mstkodeketerangan_Controller extends MVC_Controller {
	function index(){
		$this->View() ;
	}
	
	function LoadGrid($va){
		$dbData = objData::Browse($va['cTableName'],"Kode,Keterangan","","","","Kode") ;
		$n = 0 ;
		while($dbRow = objData::GetRow($dbData)){
			$va1 [++$n] = $dbRow ;
		}
		if(!empty($va1)){
			dbg::LoadArray($va1) ;
		}else{
			echo('DBGRID1.DeleteRowAll();');
		}
	}

	function SeekKode($va){
		$db = objData::ABrowse($va['cTableName'],"Kode,Keterangan","Kode = '{$va['cKode']}'") ;
		MVC::Response($db) ;
	}

	function DeleteData($va){
		objData::Delete($va['cTableName'],"Kode = '{$va['cKode']}'") ;
		MVC::Response("ok") ;
	}

	function ValidSaving($va){
		$vaArray = array("Kode"=>$va["cKode"],"Keterangan"=>$va["cKeterangan"]) ;
		objData::Update($va['cTableName'],$vaArray,"Kode = '{$va['cKode']}'") ;
		MVC::Response("ok") ;
	}
}