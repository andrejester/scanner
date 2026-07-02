<?php
  if(!defined("main")){
    define("main","1") ;
  }
  require_once "../../component/mvcload.php" ;
  eval(getVar()) ;
  
  if(empty($__par) && GetSetting('__OldPar','') !== ''){
    $__par = GetSetting('__OldPar','') ;
  }  
  
  
  if(!empty($__par)){
    SaveSetting('__OldPar',$__par) ;
    $__par = GetSetting($__par,"xx") ;
    if($__par == "xx"){
      $__par = GetSetting('__OldPar') ;
    }

    $nParam = strpos($__par,"?") ;
    if($nParam){
      $cParam = substr($__par,$nParam+1) ;
      $vaParam = split("&",$cParam) ;
      foreach($vaParam as $key=>$value){
        $va = split("=",$value) ;
        
        $value = "$" .  $va[0] . " = '" . $va [1] .  "' ;" ;
        eval($value) ;
      }
      $__par = substr($__par,0,$nParam) ;
    }    
      
    if(is_file($__par)){
      include $__par ;
      echo('<div id="__currentFile" style="width:1px;height:1px;visibility:hidden;">' . $__par . '</div>') ;
    }
    
    // Setting Background
    $vaBG   = GetBackgorund() ;  
    //if(substr($_SERVER['HTTP_HOST'],0,10) == "192.168.0."){ 
      echo('<script language="javascript" type="text/javascript">
      function CheckBody(){
        var __ob = document.body ;
        if(window.name == "mainFrame"){
          if(__ob !== null){
            if(__ob.offsetWidth <= 1240){
              cFile = "'.$vaBG['mainbgmin'].'" ;
            }else{  
              cFile = "'.$vaBG['mainbgmax'].'" ;    
            }           
            __ob.background="./wallpaper/"+cFile ;
						__ob.style.backgroundSize = "contain";
          }else{  
            setTimeout(CheckBody,500) ;
          }
        }
      }
      CheckBody() ; 
      </script>') ;
  }
?>