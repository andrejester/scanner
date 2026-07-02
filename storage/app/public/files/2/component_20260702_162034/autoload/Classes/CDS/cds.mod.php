<?php
  include 'df.php' ;
#class CDS Baru
class cds {
  #baru
  private const URL 		 = "https://cds.myassist.id/cds/public/" ;
  private const URLVault = "https://vault.myassist.id/api/file/" ;
	
  static function CreateToken(){
		//Mekanisme Token, Jika JWT Maka Kirim Data Yg Dibutuhkan Ke Server
    if(strtolower(Svr::GetConfig("token_type","redis")) == "jwt"){
			# Untuk Membuka Data Antar Server kita harus simpan Data yang mau kita Distribusikan yaitu 
			# 1. Database
			# 2. Host
			# 3. Themes

			# Database Dan Host    
			$cDatabase = GetSetting("cSession_Database") ;
			$cHost = gethostbyname(GetSetting("cSession_IP")) ;
			if($cHost == "127.0.0.1"){
				$cHost = Svr::GetServerAddr() ;
			}
			# Ambil Folder Themes untuk mengetahui Data Themes
			if(GetSetting("cSession_Themes","") == ""){
				$cDirConfig = Svr::GetDocumentRoot() . "/.themes-config" ;
				$vaFile = explode("/",dirname(Svr::GetHTTPReferer())) ;
				$cDir = $vaFile [count($vaFile)-1] ;
				$_cssfn = $cDirConfig . "/" . md5(GetSetting("cSession_UserName") . "-/" . $cDir) ;  
				$__cDir = "default" ;
				if(is_file($_cssfn)){
					$__cDir = trim(file_get_contents($_cssfn));
				}
			}else{
				$__cDir = GetSetting("cSession_Themes","default") ;
			}

			# Ambil Alamat Host Induknya kalau dibutuhkan mengambil data ke induk.
			$cMainHost = Svr::GetBaseURL() ;

			// Uploads Ke CDS
			// Daftar Variable yang akan kita komunikasikan dengan Sub Modul masukkan ke dalam Array ini.
			// Urutan Array nya harus Sama dengan Variable $vaKey di GetToken
			$vaData = [$cHost,$cDatabase,$__cDir,GetSetting("cSession_UserName"),GetSetting("cSession_UserLevel"),
								 $cMainHost,GetSetting("DBUserName"),GetSetting("DBPassword"),Svr::GetAppID(), Svr::GetConfig("rpt_data_type","mysql"),
								 Svr::GetConfig("rpt_data_ip",""),Svr::GetConfig("rpt_data_username",""),Svr::GetConfig("rpt_data_password",""),session_id()] ;

			$vaToken = JWT::encode($vaData) ;
			$cToken = $vaToken["data"] ;
		}else{
			//Jika Redis Maka Simpan AppID Saja
			$cToken		 = "svrToken".Svr::GetAppID() ;
			$vaData 	 = array("appid"=>Svr::GetAppID(),"session_id"=>session_id(),"Token"=>$cToken,"SvrConfig"=>Svr::GetConfig("*")) ;
			Svr::SaveToken($vaData) ;
		}
    return $cToken ;
  }

	static function GetToken($lUpdateToSession=true){
		$va 			= Svr::GetAllHeaders() ;
		$vaRetval = [] ;
		$cToken = isset($va["TOKEN-ID"]) ? $va["TOKEN-ID"] : "" ;
		$cToken = Svr::GetPar("__token",$cToken,false) ;

		if($cToken !== ""){
			//Cek Diredis Terlebih Dahulu
			$vaRetval = Svr::GetToken($cToken) ;
			if($lUpdateToSession){
				if(isset($vaRetval['SvrConfig'])){
					foreach($vaRetval as $key=>$value){
						Svr::SaveConfig($key,$value) ;
					}
				}
			}
			//Kalau Masih Kosong Ambil JWT
			if(empty($vaRetval)){
				$vaToken = JWT::decode($cToken) ;
				$vaData = $vaToken["data"] ;

				$vaKey = ["cSession_IP","cSession_Database","cSession_Themes","cSession_UserName",
								"cSession_UserLevel","cSession_MainHost","DBUserName","DBPassword","appid",
								"rpt_data_type","rpt_data_ip","rpt_data_username","rpt_data_password","session_id","domain"] ;
				foreach($vaData as $key=>$value){
					$cKey = isset($vaKey[$key]) ? $vaKey[$key] : $key ; 
					$vaRetval [$cKey] = $value ;
					if($lUpdateToSession){
						SaveSetting($cKey,$value) ;

						// Dia akan Mengimpan ke svr::Config 
						if(substr($cKey,0,4) == "rpt_"){
							Svr::SaveConfig($cKey,$value) ;
						}
					}				
				}
			}
		}
		return $vaRetval ;
  }

	static function GetCodeApp($cKeyApp=""){
		#fungsi ini digunakan untuk mengambil kode aplikasi pada webadmin 
		#kode aplikasi tersebut bertujuan untuk kita pakai sebagai nama file pada syslog.log
		$cURL 			= "http://webadmin.sis1.net/assist-wo.net/syslog_getdata.php" ;
		$vaMessage 	= array("MTI"=>"200","KT"=>"001","KEY"=>$cKeyApp) ;
		$vaCodeApp  = self::sendHTTPAPI($cURL,$vaMessage,200) ;
		
		return $vaCodeApp ;
	}
	
	static function sendHTTPAPI($cURL,$vaMessage,$cMTI=''){
		$ch = curl_init($cURL); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 20) ;  
		curl_setopt($ch, CURLOPT_POSTFIELDS, "cCode=" . json_encode($vaMessage));
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		$cResponse = curl_exec($ch);
		$vaResponse=  $cResponse ;    
		if(curl_error($ch)) {
			$cResponse = curl_error($ch);
			$vaResponse=  array("MTI"=>$cMTI,"RC"=>"05","MSG"=>$cResponse);   // error
		}
		curl_close($ch); 
		return $vaResponse ;
	}
	
  static function DirImg(){
    $cHost = self::URL ;
    $cDir  = Dir::GetImgFile($cHost) ;
    
    if(!is_dir($cDir)) mkdir($cDir) ;
    return $cDir ;
  }

	static function RefreshToken(){
		if($_SESSION = "undefined"){
			self::CreateToken() ;	
		}
		return self::GetToken() ;
	}

  static function GetUDF($url,$cFunc,$vaPar=array()){
		$cHost = fix_url($url) ;
    $cResult = array() ;
    $cKey = "Token" ;//self::CreateToken() ;
    $cDataType = "JSON" ;
    $vaURL = parse_url($cHost) ;
    $url = isset($vaURL ['host']) ? $vaURL ['host'] : $url ;
    $cPath = isset($vaURL ['path']) ? $vaURL ['path'] : "./" ;

    $url = fix_url($vaURL ['host']) . "/component/comp.udf.php?__token=$cKey" ;
    $cKode = json_encode(array("func"=>$cFunc,"par"=>$vaPar)) ;

    // Tambahan Customer Header
    $vaHeader = array("SIS-Path:$cPath") ;
    $cBody = self::url_go($url,$cKey,$cDataType,"",$cKode,$vaHeader) ;
    if($cBody <> ""){
      $vaBody = json_decode($cBody,true) ;      
      if(isset($vaBody ["ResponseCode"])){
        if($vaBody ["ResponseCode"] == 100){
          if(isset($vaBody ["Data"])){
            $cResult = $vaBody ["Data"] ;
          }
        }
      }
    }
    return $cResult ;
  }

  static function GetSubMenu($cSubMenu,$cHost){
		$cHost 	= fix_url($cHost);
    $vaHost = parse_url($cHost) ;
		if(!is_array($vaHost)){
			return [] ;
		}

		$lSubMVC = false ;
		if(!isset($vaHost['path']) || $vaHost['path'] == "/"){
			// Jika tidak ada path karena hanya mengisi dns atau path / maka dia jenis nya mvc contoh
			// 1. http://dev.sub.mvc.sis2.net ( berarti path tidak ada )
			// 2. http://dev.sub.mvc.sis2.net/ ( path akan berisi / )
			$lSubMVC = true ;
		}else if(isset($vaHost["path"])){
			// Jika path ada tapi path paling belakang /public atau /public/ ini juga mvc
			if(substr($vaHost["path"],-7) == "/public" || substr($vaHost["path"],-8) == "/public/"){
				$lSubMVC = true ;
			}
		}
		
		// Kalau Jenis submodule ada mvc maka cHost kita pakai semua
		$vaHeader = [] ;
		if($lSubMVC){
			$cPath = "/" ;
			if(substr($cHost,-1) == "/") $cHost = substr($cHost,0,strlen($cHost)-1) ;
			$vaHeader[] = "SIS-SUB-MVC:mvc" ;
		}else{
			$cHost = $vaHost['scheme']."://".$vaHost['host'] ;
			$cPort = isset($vaHost ['port']) ? $vaHost ['port'] : "" ;
			if($cPort !== "") $cHost .= ":" . $cPort ;
			$cPath = isset($vaHost ['path']) ? $vaHost ['path'] : "/" ;
			$vaHeader[] = "SIS-SUB-MVC:non-mvc" ;
		}
		$url = $cHost . "/component/comp.submenu.php" ;
		$vaHeader[] = "SIS-Path:$cPath" ;
    $cBody = self::url_go($url,$cSubMenu,"JSON","","",$vaHeader) ;
    $vaData = json_decode($cBody,true) ;

    $vaBody = array() ;
    if( is_array($vaData)){
      if(isset($vaData ["ResponseCode"]) && $vaData ['ResponseCode'] == 100){
        $vaBody = $vaData ["Data"] ;
      }
    }
		$cSubMVC = $lSubMVC ? "MVC" : "" ;
    return ["body"=>$vaBody,"sub_mvc"=>$cSubMVC] ;
  }

  static function SaveJSON($vaData,$url="",$cCDSID="1234567890"){
    if($url == "") $url = self::URL . "cds/json" ;
    $cKey = $cCDSID ;
    $cDataType = "JSON" ;
    $cBody = json_encode($vaData) ;
    $cBody = self::url_go($url,$cKey,$cDataType,$cBody,"") ;
    
    $vaBody = array() ;
    if($cBody <> ""){
      $vaBody = json_decode($cBody,true) ;    
    }
    return $vaBody ;
  }

  static function GetInfo(){    
    $url = self::URL . "system/user" ;
    $cKey = aCfg("msCDSID") ;
    $cDataType = "JSON" ;
    $cBody = self::url_go($url,$cKey,$cDataType,"","") ;
    if($cBody <> ""){
      $vaBody = json_decode($cBody,true) ;    
      if(isset($vaBody ["response_code"])){
        if($vaBody ["response_code"] == 100){
          if(isset($vaBody ["data"])){
            $cResult = $vaBody ["data"] ;
          }
        }
      }
    }
    return $cResult ;
  }

  static function GetJSON($cKode,$cDefault="",$cCDSID="1234567890"){
    $cResult = $cDefault ;
    
    $url = self::URL . "cds/json" ;
    $cKey = $cCDSID ;
    $cDataType = "JSON" ;
    $cBody = self::url_go($url,$cKey,$cDataType,"",$cKode) ;
    if($cBody <> ""){
      $vaBody = json_decode($cBody,true) ;    
      if(isset($vaBody ["response_code"])){
        if($vaBody ["response_code"] == 200){
          if(isset($vaBody ["data"])){
            $cResult = $vaBody ["data"] ;
          }
        }
      }
    }
    return $cResult ;
  }
  
  static function SaveSession($vaData,$cKey=""){
    $url = self::URL . "cds/session" ;
    if($cKey == "") $cKey = session_id() ;
    $cDataType = "SESSION" ;
    $cBody = json_encode($vaData) ;
    $cBody = self::url_go($url,$cKey,$cDataType,$cBody,"") ;

    $vaBody = array() ;
    if($cBody <> ""){
      $vaBody = json_decode($cBody,true) ;
    }
    return $vaBody ;
  }

  static function GetSession($cKode="*",$cKey=""){
    $cResult = array() ;
    $url = self::URL . "cds/session" ;
    if($cKey == "") $cKey = session_id() ;
    $cDataType = "SESSION" ;
    
    $cBody = self::url_go($url,$cKey,$cDataType,"",$cKode) ;
    if($cBody <> ""){
      $vaBody = json_decode($cBody,true) ;
      #baru
      if(isset($vaBody ["response_code"])){
        if($vaBody ["response_code"] == 200){
          if(isset($vaBody ["data"])){
            $cResult = $vaBody ["data"] ;
          }
        }
      }
    }
		
    return $cResult ;
  } 
    
  static function UploadFileTmp($vaBody,$cCDSID,$cDataType){
    $url  = self::URL . "cds/filetmp" ;
    $cKey = $cCDSID ;
    
    $cBody = self::url_go($url,$cKey,$cDataType,$vaBody,"") ;
    return $cBody ;
	}

	static function UploadFile($vaBody,$cCDSID,$cDataType,$nVaultToken = ""){
		if($nVaultToken <> ""){			
			$url 		= self::URLVault . "upload";
			return self::url_govault($url,$nVaultToken,[],"POST",["file" => $vaBody]);
		}

		$url   = self::URL . "cds/file";
		$cKey  = $cCDSID; 
		$cBody = self::url_go($url, $cKey, $cDataType, $vaBody, "");
		return $cBody;
	}
   
	static function GetFile($cKode,$cCDSID,$cDataType,$cURLType="",$nVaultToken="",$vaID=[]){ 
		if(!empty($vaID) && $nVaultToken <> ""){  			 
			$url 			 = self::URLVault . "magiclink";
			$header 	 = ["Content-Type: application/json"];
			$response  = self::url_govault($url,$nVaultToken,$header,"POST",json_encode(["id"=>$vaID]));
			if(!empty($response['data']['success'])){
				return array_column($response['data']['success'], 'url');
			}
		}

		$url       = self::URL . "cds/file";
		$cKey      = $cCDSID;
		$cKode     = $cKode;
		$cBody 	   = self::url_go($url, $cKey, $cDataType, "", $cKode, [], $cURLType);

		return $cBody;
  }
  
	static function GetFileSurat($cKode,$cCDSID,$vaData=array()){
		$url       = self::URL . "cds/file" ;
    $cKode     = $cKode;
		$lPDF 		 = aCfg::Get("msSuratPDF",0);
		$cSQLCekTeble = "SELECT COUNT(*) AS jumlah FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'surat' AND COLUMN_NAME = 'UbahPDF'";
		if(objData::GetRow(objData::SQL($cSQLCekTeble))["jumlah"] > 0){
			$vaCekPDF = objData::ABrowse("surat","UbahPDF","FileName = '$cKode'");
			if(count($vaCekPDF) >0){
				$lPDF = $vaCekPDF[0]["UbahPDF"];
			}
		}
		$cContent = json_encode(array("lSuratPDF"=>$lPDF,"lContent"=>true,"vaContent"=>$vaData),JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) ;
    $cBody = self::url_go($url,$cCDSID,"surat",$cContent,$cKode,array(),"","GET") ;
		return $cBody ;
	}
	  
	static function DeleteFile($cKode,$cCDSID,$cDataType,$nVaultToken="",$cID=""){    
		if($cID <> "" && $nVaultToken <> ""){  
			$apiUrl = self::URLVault . "delete/" . $cID;
			return self::url_govault($apiUrl,$nVaultToken,[],"DELETE"); 
		}
		
    $url   = self::URL . "cds/file" ;
    $cKey  = $cCDSID ;
    $cKode = $cKode ;

    $cBody = self::url_delete($url,$cKey,$cDataType,$cKode) ;
    return $cBody ;
  }
  
  private static function url_go($url,$cKey,$cDataType,$cBody,$cKode,$vaHeader=[],$cUrlType="",$lMethod="POST"){
		$vaFromCall    = debug_backtrace(); 
    $cFromFunction = isset($vaFromCall[1]['function']) ? $vaFromCall[1]['function'] : "" ;
		$cFilePath     = isset($vaFromCall[1]['file']) ? $vaFromCall[1]['file'] : "" ;
		if (!strpos($cFilePath, 'rpt_header') !== false) {
			SysLog::Report("10",array("Kode"=>"$cFromFunction $cKode","Field"=>$cBody)) ;
		}
		$cTime = date("c") ;
    $cVersion = "1.0" ;
    if($cDataType <> "img" && $cDataType <> "file" && $cDataType <> "surat"){
      $cSignature = md5("$cKey:$cTime:$cVersion:$cBody") ;
			$vaHeader[] = "Content-Type:application/json" ; 
			$vaHeader[] = "SIS-Kode:$cKode" ; 
    }else{
      $cSignature = md5("$cKey:$cTime:$cVersion:") ;
      if($cBody == ""){
        $vaHeader[] = "Content-Type:application/json" ;
				$vaHeader[] = "SIS-Kode:$cKode" ;
				$vaHeader[] = "SIS-Url-Type:$cUrlType" ;
      }
    }
		if($lMethod == "GET"){
			$cSignature = md5("$cKey:$cTime:$cVersion:$cBody") ;
			$vaHeader[] = "Content-Type:application/json" ;
			$vaHeader[] = "SIS-Kode:$cKode" ;
			$vaHeader[] = "SIS-Url-Type:$cUrlType" ;
			
		} 
		
		$vaHeader[] = "SIS-Signature:$cSignature" ;
		$vaHeader[] = "SIS-Version:$cVersion" ;
		$vaHeader[] = "SIS-Timestamp:$cTime" ;
		$vaHeader[] = "SIS-Data-Type:$cDataType" ;
    $vaHeader[] = "SIS-Key: $cKey" ;
		$vaHeader[] = "SIS-Token:Token" ;// . cds::CreateToken() ;
		 
		$cURL = curl_init();
		curl_setopt($cURL, CURLOPT_SSL_VERIFYHOST, 2);
		//curl_setopt($cURL, CURLOPT_CAINFO, __DIR__."/certificate/myassist.id.ca");
		curl_setopt($cURL, CURLOPT_URL, $url);
    if($cBody <> ""){
      curl_setopt($cURL, CURLOPT_POST, true);
      curl_setopt($cURL, CURLOPT_POSTFIELDS, $cBody);
      curl_setopt($cURL, CURLOPT_CUSTOMREQUEST,$lMethod) ;
    }else{
      curl_setopt($cURL, CURLOPT_CUSTOMREQUEST,'GET') ;
    }
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($cURL, CURLOPT_HTTPHEADER,$vaHeader);
		$cBody = curl_exec($cURL);
    curl_close($cURL);
    return $cBody ;
  }
  
  private static function url_delete($url,$cKey,$cDataType,$cKode){
		$vaFromCall    = debug_backtrace(); 
    $cFromFunction = isset($vaFromCall[1]['function']) ? $vaFromCall[1]['function'] : "" ;
		SysLog::Report("10",array("Kode"=>"$cFromFunction $cKode")) ;
		
    $cTime = date("c") ;
    $cVersion = "1.0" ;
    $cSignature = md5("$cKey:$cTime:$cVersion:") ;
    $cURL = curl_init();  
    curl_setopt($cURL, CURLOPT_URL, $url);
    curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($cURL, CURLOPT_CUSTOMREQUEST,'DELETE') ;
    curl_setopt($cURL, CURLOPT_HTTPHEADER,
                array(
                  "Content-Type:application/json",
                  "SIS-Key: $cKey",
                  "SIS-Data-Type:$cDataType",
                  "SIS-Timestamp:$cTime",
                  "SIS-Version:$cVersion",
                  "SIS-Signature:$cSignature",
                  "SIS-Kode:$cKode",
                )
               );
    $cBody = curl_exec($cURL);
    curl_close($cURL);
    return $cBody ;
  }
	
	private static function url_govault($url,$cKey,$vaHeader = [],$lMethod = "POST",$vaBody = []){
		$ch = curl_init();

		$defaultHeader = [
			"Authorization: Bearer $cKey",
			"Accept: application/json"			
		];

		$headers = array_merge($defaultHeader,$vaHeader);
    
		curl_setopt_array($ch, [ 
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CUSTOMREQUEST  => $lMethod,
			CURLOPT_HTTPHEADER     => $headers,
			CURLOPT_POST           => true,       
			CURLOPT_POSTFIELDS     => !empty($vaBody) ? $vaBody : null,
		]);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$error    = curl_error($ch);

		curl_close($ch);

		return [
			"raw_response" => $response,
			"data"         => json_decode($response, true),
			"httpCode"     => $httpCode,
			"error"        => $error
		];
	}

}