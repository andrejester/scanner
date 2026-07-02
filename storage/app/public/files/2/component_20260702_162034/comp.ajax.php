<?php
  include 'df.php' ;
	$vaArrayFc = array("UpdateTimes()");
	$__par = Svr::GetPar("__par","",false) ;
  if(!empty($__par)){
		eval(Conv::GetVar([],false)) ;

    SaveSetting('__OldPar1',$__par) ;
    $__par = GetSetting($__par,"xx") ;
    if($__par == "xx") $__par = GetSetting('__OldPar1') ;
    
    $nParam = strpos($__par,"?") ;
    if($nParam){
      $cParam = substr($__par,$nParam+1) ;
      $vaParam = explode("&",$cParam) ;
      foreach($vaParam as $key=>$value){
        $va = explode("=",$value) ;

        $value = "$" .  $va[0] . " = '" . $va [1] .  "' ;" ;
        eval($value) ;
      }
      $__par = substr($__par,0,$nParam) ;
    }
    if(is_file($__par)){
      include $__par ;
      if(!empty($cKey)){
        $nFunc = strpos($cKey,"(") ;
        if($nFunc){
          $cEval = substr($cKey,0,$nFunc + 1) . "\$" . "_POST" ;
          $cEval1 = substr($cKey,$nFunc + 1) ;
          if(trim($cEval1) <> ")"){
            $cEval1 = "," . $cEval1 ;
          }
          $cEval = $cEval . $cEval1 . " ;" ;
					if(!in_array($cKey,$vaArrayFc)){
            SaveSetting("cSession_ActivityUser",date('D M d Y H:i:s O'));
          }
          eval($cEval) ;
        }
      }
    }else{
			//Jika File Tidak Ada Maka Dipastikan Itu SaveLog , Validasi Variable MTI dan Messagenya Untuk Memastikan
			if(isset($cMTI) && isset($cMessage)) SysLog::SaveLog($_POST) ;
		}
  }
?>