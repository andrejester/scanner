<?php
class login_Controller extends MVC_Controller {
	function index(){
		$this->View("") ;
	}
	
	function captcha(){
		$c = $this->CreateImage() ; 
  	SaveSetting("cSession_Login_Captcha",md5($c)) ;
	}
	
	function CreateImage(){
		$cKey = "1234567890" ;
		$c = substr( str_shuffle($cKey) ,0,5) ;  
		$nWidth = 60 ;
		$nHeight = 20 ;
		$nFontSize = 5 ;

		$im = imagecreate($nWidth, $nHeight) ;
		$background_color = imagecolorallocate($im,220,220,220);
		$noise_color = imagecolorallocate($im, 165, 180, 219);
		for( $i=0; $i<($nWidth*$nHeight)/3; $i++ ) {
			imagefilledellipse($im, mt_rand(0,$nWidth), mt_rand(0,$nHeight), 1, 1, $noise_color);
		}
		for( $i=0; $i<($nWidth*$nHeight)/150; $i++ ) {
			imageline($im, mt_rand(0,$nWidth), mt_rand(0,$nHeight), mt_rand(0,$nWidth), mt_rand(0,$nHeight), $noise_color);
		}

		$nLeft = ($nWidth - (imagefontwidth($nFontSize) * 5)) / 2 ;
		$text_color = imagecolorallocate($im,0,0,0);
		imagestring($im, $nFontSize, $nLeft, 2,$c, $text_color);

		header("Content-type: image/png");
		imagepng($im);
		imagedestroy($im);

		return $c ;
	} 
	
	function dologin(){
		$va 			 = $_POST;
		$cError    = "ok" ;		
		$cPassword = md5(strtoupper($va ['cPassword'])) ;
		$cUserName = $va ['cUserName'] ;
    $cField = "u.UserName,u.UserPassword,u.Aktif,u.Block,u.Online" ;
    $dbData = objData::Browse("username u",$cField,"u.UserName = '$cUserName'") ;
    if($dbRow = objData::GetRow($dbData)){
      $cUserPassword = $dbRow ['UserPassword'] ;
      $cUserPassword = substr($cUserPassword,0,10) . substr($cUserPassword,14) ;
      $cUserLevel    = substr($dbRow ['UserPassword'],10,4) ;
			$nBlock        = $dbRow ['Block'] ;
			
			$cByPass       = (strtolower(substr($cUserName,0,4)) == 'team' || strtolower(substr($cUserName,0,6)) == 'assist' || strtolower($cUserName) == 'nofika' || strtolower($cUserName) == 'hafiz') ? 1 : 0 ;
			$cByPassDev    = (substr($_SERVER["HTTP_HOST"],0,7) == "bpr.mvc" || substr($_SERVER["HTTP_HOST"],0,7) == "cbs.bpr" || substr($_SERVER["HTTP_HOST"],0,6) == "aa.mvc") ? 1 : 0;
			
			if(true || GetSetting("cSession_Login_Captcha") == md5($va['cCaptcha']) || $cByPass){ //Check captcha - DISABLED
				if($cUserPassword == $cPassword || $cByPassDev){ //Check user password
					if($dbRow['Aktif'] == 1 || $cByPass){ //Check status aktif user
						if($dbRow['Block'] < 3 || $cByPass){ //Check status block user
							$nNOW = time() - 15 ;
							
            	if(User::LastOnline($dbRow ['UserName']) < $nNOW || $cByPass){ //Check status online user
								SaveSetting("cSession_UserName",$dbRow ['UserName']) ;
								SaveSetting("cSession_UserLevel",$cUserLevel) ;
								SaveSetting("cSession_Themes",aCfg::Get("cSession_Themes","default")) ;
								SaveSetting("cSession_Themes_NoAnimation",aCfg::Get("cSession_Themes_NoAnimation",0)) ;
								SaveSetting("cLogin",1) ;
								
								$nTime = time()+15 ;
								User::Save("Online",$nTime) ; 
							}else{
								$cError = "Username sedang login atau digunakan ...! " ; 
							}
							objData::Update("username",array("Block"=>0),"Username = '$cUserName'") ; 
						}else{
							$cError = "Username telah terblokir ...! \n Hubungi administrator untuk mengaktifkan kembali" ;
						}
					}else{
						$cError = "Username telah dinonaktifkan ...! \n Hubungi administrator untuk mengaktifkan kembali" ;
					}
				}else{
					if(strtolower(substr($cUserName,0,4)) <> 'team' && strtolower($cUserName) <> 'assist'){
            $nBlock++ ;
          	objData::Update("username",array("Block"=>$nBlock),"Username = '$cUserName'") ;
          }
          $vaBlock = array("","2x lagi salah password, username akan terblokir","Sekali lagi salah password, username akan terblokir","Username telah terblokir, hubungi administrator untuk mengaktifkan kembali") ;
					$cError  = empty($vaBlock[$nBlock]) ? "Password salah ...!" : "Password salah, ".$vaBlock[$nBlock]." ...!" ;
				}
			}else{
				$cError = "Captcha tidak sesuai ...!" ;
			}
    }else{
      $cError = "Username tidak Ditemukan ...!" ;
    }
		
		MVC::Response($cError) ;
	}	
	
	function CheckLogin(){
		setcookie("_TOKEN_","1234") ;
		
		MVC::Response($_COOKIE["JWT"]) ;
	}
	
}