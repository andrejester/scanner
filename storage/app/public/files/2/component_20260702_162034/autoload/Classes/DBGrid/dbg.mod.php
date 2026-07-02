<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class dbg {
  private static $d = [] ;
  public static $AddColumn = array() ;
  public static $Array = array() ;
  public static $Height = "200px" ;
  public static $Col = [] ;
  public static $Width = "100%" ;
  public static $Name = "" ;
  private static $count = 0 ;
  public static $Caption = "" ;
  public static $onClick = "" ;
  public static $ShowFooter = false ;
  public static $AutoWidth = false ;
  public static $BorderColor = "#56569d" ;
  public static $Scrolling = "auto" ;
	public static $ShowToolbar = false ;
	public static $ColFreeze = 0 ;
  static function Init(){
		self::$d = ["conf"=>["height"=>"200px",
												"width"=>"100%",
												"name"=>"",
												"showFooter"=>false,
												"autoWidth"=>false,
												"scrolling"=>"auto",
												"borderColor"=>"#56569d",
												"showFooter"=>false,
												 ],
								"caption"=>[],"header"=>[],"body"=>[],"footer"=>[],"data"=>[]] ;
    self::$AddColumn = array() ;
    self::$Array = array() ;
    self::$Height = "200px" ;
    self::$Col = array() ;
    self::$Width = "100%" ;
    self::$Name = "" ;
    self::$Caption = "" ;
    self::$onClick = "" ;
    self::$ShowFooter = false ;
    self::$AutoWidth = false ;
    self::$BorderColor = "#56569d" ;
    self::$Scrolling = "auto" ;
		self::$ShowToolbar = false ;
		self::$ColFreeze = 0 ;
  }

	static function AutoWidth($lAutoWidth=false){
		self::$AutoWidth = $lAutoWidth ;
	}

  static function dataSource($dbData){
		$vaArray = objData::FetchAssoc_All($dbData) ;
		self::$Array = $vaArray ;
		return $vaArray ;
  }

  static function SQL($cSQL){
    $dbData = objData::SQL($cSQL) ;
    return self::dataSource($dbData) ;
  }

	static function GridContent($cJSON){
		return json_decode($cJSON,true) ;
  }
	
	/*
	private static function assoc2num($va){
		$data = [] ;
		foreach($va as $row=>$vaRow){
			$data[$row] = $vaRow ;
			if(is_array($vaRow)){
				$data[$row] = [] ;
				foreach($vaRow as $value){
					$data[$row][] = str_replace('"','\"',$value) ;
				}
			}
		}
		return $data ;
	}
	*/
	
	private static function assoc2num($va){
		$data = array_values($va) ;
		foreach($data as $key=>$value){
			$data[$key] = array_values($value) ;
		}
		return $data ;
	}

	static function dataBind(){
		if(empty(self::$Name)){
      self::$count ++ ;
			self::$d["conf"]["name"] = "DBGRID" . self::$count ;
    }else{
			self::$d["conf"]["name"] = self::$Name ;
		}

		self::$d["conf"]["height"] = self::$Height ;
		self::$d["conf"]["width"] = self::$Width ;		
		self::$d["conf"]["showFooter"] = self::$ShowFooter ;
		self::$d["conf"]["autoWidth"] = self::$AutoWidth ;
		self::$d["conf"]["scrolling"] = self::$Scrolling ;
		self::$d["conf"]["borderColor"] = self::$BorderColor ;
		self::$d["conf"]["url"] = Svr::GetComponentURL() . substr(__DIR__,strlen(Svr::GetComponentPath(true))+1) ;
		self::$d["conf"]["showToolbar"] = self::$ShowToolbar ;
		self::$d["conf"]["colFreeze"] = self::$ColFreeze ;
		self::$d["caption"] = self::$Caption ;
		self::$d["hIndex"] = [] ;
		
		// Jika AddColumn kosong kita coba cari di $Array kalau ada kita ambil nama colom nya
		if(count(self::$AddColumn) == 0){
			if(count(self::$Array) > 0){
				foreach(self::$Array as $value){
					foreach($value as $col=>$field){
						self::$AddColumn [] = $col ;
					}
					break ;
				}
			}
		}

		// Defaultnya kalau autowidth maka lebar kolom adalah -1, biar lebar mengikuti content
		$nWidth = self::$AutoWidth ? -1 : 100 ;
		foreach(self::$AddColumn as $key=>$value){
			self::$d["header"][$value] = ["cellIndex"=>$key,"width"=>$nWidth,"edit"=>false,"align"=>"left","type"=>"text","caption"=>null] ;
			self::$d["hIndex"][] = $value ;
		}

		$vaLower = ["align"=>1,"type"=>1] ;
		foreach(self::$Col as $key=>$value){
			if(isset(self::$d["header"][$key])){			// Untuk Configurasi Colom hanya untuk kolom yang ada di addcolom kalau tidak maka kita abaikan.
				foreach($value as $key2=>$value2){
					$key2 = strtolower($key2) ;
					if($key2 <> "caption") $value2 = strtolower($value2) ;
					if(isset($vaLower[$key2])) $value2 = strtolower($value2) ;
					self::$d["header"][$key][$key2] = $value2 ;
				}
			}			
		}
		//Jika type number maka otomatis isi rata kanan dan number format seperti component lama
		//rata kanan dan numbercol
		foreach(self::$d["header"] as $key=>$value){
			if(strtolower($value["type"]) == "number"){
				self::$d["header"][$key]["align"] = "right" ;
				self::$d["numbercol"][$key] = 1 ;
			}  
		}
		//array value berdasarkan numbercol agar tidak diforeach beberapa kali pada self::array
		if(isset(self::$d["numbercol"])){
			foreach(self::$Array as $key=>$value){
				foreach($value as $key2=>$value2){
					if(isset(self::$d["numbercol"][$key2])) self::$Array[$key][$key2] = Number2String($value2) ;
				}
			}
		}
		self::$d["data"] = count(self::$Array) > 0 ? json_encode(self::assoc2num(self::$Array)) : "" ;
		$_va[] = self::$d ;
		$c = "<div id='_dbg_div_main_' style='display:none'>" . json_encode($_va) . "</div>" ;
		echo($c) ;
		Mod::ImportJS("dbg") ;
		self::Init() ;
	}

	static function Col($cColName,$nWidth=100,$cAlign="left",$cCaption=null,$cType="text",$lEdit=false,$cDisplay="show",$cFooterText="",$cFooterAlign="left"){
		self::$Col [$cColName] = ["width"=>$nWidth,"align"=>$cAlign,"caption"=>$cCaption,"type"=>strtolower($cType),"edit"=>$lEdit,"display"=>$cDisplay,"footerText"=>$cFooterText,"footerAlign"=>$cFooterAlign] ;
		return self::$Col [$cColName] ;
	}

  static function LoadArray($vaArray,$cGridName='DBGRID1'){
    if(!empty($vaArray)){
			if(!is_array($vaArray)){
				$vaArray = objData::FetchAssoc_All($vaArray) ;
			}

			// Field kita pindah ke array dengan key 0,1,2 bukan nama field untuk mempertahankan konsistensi posisi kolom
			$vaData = self::assoc2num($vaArray) ;
			$c = json_encode($vaData) ;
			$c = str_replace("\\","\\\\",$c) ;
			$c = str_replace("'","\'",$c) ;
			echo("$cGridName.LoadArray('$c') ;") ;
    }
    echo("if(typeof " . $cGridName . "_onAfterLoadArray == 'function') " . $cGridName . "_onAfterLoadArray() ;");
  }
}