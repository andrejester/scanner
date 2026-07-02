<?php
  include 'df.php' ;
  $subMenu = Svr::IsMVC() ? SisConfig::GetValue("mnuSubMenu") : "" ;
	menu::SubMenuFile($subMenu) ;
	$vaMenu = menu::mnu2Array() ;
	$cLevel = md5($data['cKodeLevel']) ;
  $dbData = objData::Browse("username_menu","*","Level = '$cLevel'") ;
  $vaLevel =objData::FetchAssoc_All($dbData,"Keterangan") ;
	$pdf = new Cezpdf('LEGAL','poertrait');
	//Header    
  
	$vaUser      = Func::GetDataUser(GetSetting("cSession_UserName")) ;
  $vaInfo      = GetInfoBank::Get($vaUser['Cabang']) ;
	$cKodeCabang = $vaUser['Cabang'] ;
	//include '../../sub-bpr/sub-bpr-system/laporan/rpt_header.report.php' ;
 
	$pdf->ezPageHeader("Laporan Akses Menu Level ".$data['cKeteranganLevel'],array('fontSize'=>15,'justification'=>'center')) ;
  $pdf->ezPageHeader(" ",array('fontSize'=>8,'justification'=>'center')) ;

  GenerateMenu($vaMenu,1,$pdf,$vaLevel,$data['cKodeLevel']) ;
	function GenerateMenu($vaMenu,$tab =1,$pdf=array(),$vaLevel =array(),$cLevel =""){
	  foreach ($vaMenu as $key => $value) {
			foreach($value as $key1 => $value1){
				if(isset($value1['mnuTitle'])){
					$lReturn = isset($vaLevel[$value1['mnuID']]) || isset($vaLevel[$value1['oldMnuID']]) || $cLevel == "0000" ? true : false;
					if($lReturn && $value1['mnuTitle'] != '-'){
						$vaNo = explode(".",$value1['mnuNumber']) ;
					  $tab = (count($vaNo)+3) *2;
					  $data = str_repeat(" ", $tab).$value1['mnuNumber']." ".$value1['mnuTitle'] ;
						$pdf->ezText($data);
						//$pdf->ezPageHeader($data);
					} 
				}else{
					GenerateMenu($value1,$tab,$pdf,$vaLevel,$cLevel) ;
				}
		  }
		}
	}

	$pdf->ezStream() ;
?> 