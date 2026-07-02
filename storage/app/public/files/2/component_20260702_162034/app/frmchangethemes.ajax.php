<?php
  include 'df.php' ;

if(Svr::IsMVC() && isset($_POST["cFolder"])) {
	ApplyThemes($_POST) ;
} 

function ApplyThemes($va){
	if(Svr::IsMVC()){
		aCfg::Upd("cSession_Themes",$va["cFolder"]) ;
		SaveSetting("cSession_Themes",$va["cFolder"]) ;
		
		aCfg::Upd("cSession_Themes_NoAnimation",$va['optAnimasi']) ;
		SaveSetting("cSession_Themes_NoAnimation",$va["optAnimasi"]) ;
	}else{
		$cDirConfig = Dir::DataDir(".themes-config") ;

		$_cssfn = $cDirConfig . "/" . md5(GetSetting("cSession_UserName")) ;
		if(is_file($_cssfn)) unlink($_cssfn) ;        
		$handle = fopen($_cssfn, "w");
		fwrite($handle,$va ['cFolder']."|".$va['optAnimasi']) ;
		fclose($handle) ;
		SaveSetting("cSession_Themes","") ;
		//SaveSetting("cSession_Themes_NoAnimation","") ;
	}
  echo("ok") ;
}
?>