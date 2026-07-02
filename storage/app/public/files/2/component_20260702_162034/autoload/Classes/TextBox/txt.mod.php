<?php

class txt{
  public static $MaxLength = 0 ;
  public static $Width = 20 ;
  public static $Value = "" ;
  public static $Name = "" ;
  public static $ReadOnly = false ;  
  public static $Type = "Text" ;
  public static $Button = false ;
  public static $ButtonClick="" ;
  public static $onButtonClick="" ;
  public static $Caption = "" ;
  public static $LCaption = "" ;
  public static $LCapWidth = "100px" ;
  public static $LCapSemiColon = true ;
  public static $Checked = false ;
  public static $Class = "" ;
  public static $Style = "" ;
  public static $Disabled = false ;
  public static $ID = 0 ;
  public static $PlaceHolder = "" ;

  public static $onClick = "" ;
  public static $onBlur="" ;
  public static $onChange = "" ;
  public static $onDblClick = "" ;
  public static $onFocus = "" ;
  public static $onKeyDown = "" ;
  public static $onKeyPress = "" ;
  public static $onKeyUp = "" ;
  public static $onMouseDown = "" ;
  public static $onMouseMove = "" ;
  public static $onMouseOut = "" ;
  public static $onMouseOver = "" ;
  public static $onMouseUp = "" ;
  public static $onSelect = "" ;

	private static $txtID = 0 ;
	private static $required = ["required"=>false,"isSetting"=>false] ;

  private static function myDir(){
    $cDir = __DIR__ ;
    $cRoot = Svr::GetDocumentRoot() ;
    $cDir = "../" . substr($cDir,strlen($cRoot)+1) ;
    return $cDir ;
  }

  private static function Init(){
    self::$MaxLength = 0 ;
    self::$Width = 20 ;
    self::$Value = "" ;
    self::$Name = "" ;
    self::$ReadOnly = false ;  
    self::$Disabled = false ;
    self::$Type = "text" ;
    self::$Button = false ;
    self::$ButtonClick="" ;
    self::$onButtonClick="" ;
    self::$Caption = "" ;
    self::$LCaption = "" ;
    self::$LCapWidth = "100px" ;
    self::$LCapSemiColon = true ;
    self::$Checked = false ;
    self::$Class = "" ;
    self::$Style = "" ;
    self::$PlaceHolder = "" ;

    self::$onBlur="" ;
    self::$onChange = "" ;
    self::$onClick = "" ;
    self::$onDblClick = "" ;
    self::$onFocus = "" ;
    self::$onKeyDown = "" ;
    self::$onKeyPress = "" ;
    self::$onKeyUp = "" ;
    self::$onMouseDown = "" ;
    self::$onMouseMove = "" ;
    self::$onMouseOut = "" ;
    self::$onMouseOver = "" ;
    self::$onMouseUp = "" ;
    self::$onSelect = "" ;
		self::$required = ["required"=>false,"isSetting"=>false] ;
  }

	static function Required($status=true,$char="*",$min="",$max="",$num_decimal=0,$date_format="dd-mm-YYYY",$pattern="",$title=""){
		self::$required = ["required"=>$status,"isSetting"=>true,"char"=>$char,"min"=>$min,"max"=>$max,"num_decimal"=>$num_decimal,"date_format"=>$date_format,"pattern"=>$pattern,"title"=>$title] ;
	}

  private static function updPar($cFunc){
    return rawurlencode(str_replace('this',"a.getById('txt-" . self::$ID . "')",$cFunc)) ;
		//return rawurlencode(str_replace('this',"f",$cFunc)) ;
  }

  static function HiddenField($cName='',$cValue=''){
    self::$Type = "hidden" ;
    self::Show($cName,$cValue) ;
  }

  static function ButtonField($cName='',$cValue='',$lReadOnly=''){
    self::$Type = "button" ;
    self::$Class = "Button" ;
    self::Show($cName,$cValue,0,0,$lReadOnly) ;
  }

  static function NumberField($cName='',$cValue='',$nMaxLength=0,$nWidth=0,$lReadOnly='',$nMinValue=0,$nMaxValue=0,$nDecimal=0){
		self::$Type = "number" ;
		if(!self::$required["isSetting"]){
			$lRequired = $nMinValue !== 0 || $nMaxValue !== 0 ;
			self::Required($lRequired,"*",$nMinValue,$nMaxValue,$nDecimal) ;
		}
		
    self::Show($cName,$cValue,$nMaxLength,$nWidth,$lReadOnly) ;
  }
	
  static function DateField($cName='',$cValue='',$lReadOnly='',$min_Date='',$max_Date=''){
		Mod::ImportJS("cal") ;
		self::$Type = "date" ;
		if(!self::$required["isSetting"]){
			$lRequired = $min_Date !== "" || $max_Date !== "" ;
			self::Required($lRequired,"*",$min_Date,$max_Date) ;			
		}
		self::Show($cName,$cValue,0,0,$lReadOnly) ;
  }

  static function RadioButton($cName='',$cValue='',$lReadOnly='',$cCaption='',$lChecked=false){
    self::$Type = "radio" ;
		if(self::$Caption == "" && $cCaption !== "") self::$Caption = $cCaption ;
		if(!self::$Checked) self::$Checked = $lChecked ;
    self::Show($cName,$cValue,0,0,$lReadOnly) ;
  }

  static function CheckBox($cName='',$cValue='',$lReadOnly='',$cCaption='',$lChecked=false){
    self::$Type = "checkBox" ;
		if(self::$Caption == "" && $cCaption !== "") self::$Caption = $cCaption ;
		if(!self::$Checked) self::$Checked = $lChecked ;
    self::Show($cName,$cValue,0,0,$lReadOnly) ;
  }

	private static function _txtID(){
		return ++self::$txtID . "-" . time() . "_" . rand(0,999999) ;
	}

  static function Show($cName='',$cValue='',$nMaxLength=0,$nWidth=0,$lReadOnly='',$lButton=false,$cCaption="",$cPlaceHolder=""){
    $cTxtType = strtolower(self::$Type) ;
    if(empty($cName)) $cName = self::$Name ;
    if($cValue == '') $cValue = self::$Value ;
    if(empty($nMaxLength)) $nMaxLength = self::$MaxLength ;
    if(empty($nWidth)) $nWidth = self::$Width ;
    if(empty($lReadOnly)) $lReadOnly = self::$ReadOnly ;
		if(!self::$Button) self::$Button = $lButton ;
		if(self::$Caption == "" && $cCaption !== "") self::$Caption = $cCaption ;
		if(self::$PlaceHolder == "" && $cPlaceHolder !== "") self::$PlaceHolder = $cPlaceHolder ;
		
    self::$Name = $cName ;
    self::$ID = self::_txtID() ;

    $cReadOnly = " " ;
    if(self::$Disabled) $cReadOnly = ' disabled' ;

    $cStyle = "" ;
    if($cTxtType == "number"){
      $cStyle = ";text-align:right" ;      
      if($cValue == "") $cValue = "0" ;
    }

    $cButton = "" ;    
    if(self::$Button || $cTxtType == "date"){
			Mod::ImportJS("txtbtn") ;		// Akses Modul Button
			if($cTxtType == "date") self::$PlaceHolder = "dd-mm-yyyy" ;

			$urlImg = self::myDir() . "/images/" ;
			if(Svr::IsMVC()){
				$urlImg = Svr::GetComponentURL() . substr(__DIR__,strlen(Svr::GetComponentPath(true))+1) . "/images/" ;				
			}
			$cImgName = $urlImg . "pick-button.gif?_th=auto" ;
      $cLink = "" ;
			
      if($cTxtType == "date"){
        $cStyle .= ";width:89px" ;
        $cLink = "showCal(document.form1." . $cName . ")" ;
        $nWidth = 10 ;
        $nMaxLength = 11 ;
				$cImgName = $urlImg . "date-button.gif?_th=auto" ;
      }else if(self::$ButtonClick == ''){
        self::$ButtonClick = self::$onButtonClick ;
      }
			$cButton = '<img class="input-button" src="' . $cImgName . '" onMouseDown="txt.bmd(\'' . self::$ID . '\');" onClick="txt.bClick(\'' . self::$ID . '\',\'' . $cTxtType . '\',\'' . self::updPar(self::$ButtonClick) . '\')" align="top">' ;
    }

    $cClass = "" ;
    if(!empty(self::$Class)) $cClass = ' Class="' . self::$Class . '"' ;

    $cType = "Text" ;
    $cSize = ' size="' . $nWidth . '" ' ;
    $cMaxLength = "" ;
    if(!empty($nMaxLength)) $cMaxLength = ' maxlength="' . $nMaxLength . '" ' ;
    $cChecked = "" ;
    if($cTxtType == "radio"){
      $cStyle = ";height:14px;width:14px;border-width:0px" ;
      $cType = "radio" ;
      $cSize = '' ;
      $cMaxLength = '' ;
      if(self::$Checked) $cChecked = ' checked ' ;
    }else if($cTxtType == "checkbox"){  // Check Box      
      $cStyle = ";height:14px;width:14px;border:1px solid #b8bab3" ;
      $cType = "checkbox" ;
      $cSize = '' ;
      $cMaxLength = '' ;
      if(self::$Checked) $cChecked = ' checked ' ;
    }else if($cTxtType  == "button"){    // Jika Button
      self::$Button = false ;
      $cType = "button" ;
      $cSize = '' ;
      $cMaxLength = '' ;
    }else if($cTxtType  == "hidden"){
      $cType = "hidden" ;
      $cSize = '' ;
      $cMaxLength = '' ;
      $cClass = '' ;
      $cChecked = '' ;      
    }else if($cTxtType == "password"){
      $cType = "password" ;
    }else if($cTxtType == "file"){
      $cType = "file" ;
    }

    if(trim(self::$Style) !== "") $cStyle .= ";" . self::$Style ;
    $nStyleWidth = strpos(strtolower($cStyle),"width") ;
    if($nStyleWidth === false){
      $nWidthStyle = $nWidth ;
      if($nWidthStyle == 0) $nWidthStyle = 20 ;
      if($cTxtType == "text" || $cTxtType == "number" || $cTxtType == "password"){
				$txtWidth = (($nWidthStyle*7) + 18) ;
				if(self::$Button) $txtWidth += 4 ;
        $cStyle .= ";width:" . $txtWidth . "px;" ;
      }else if($cTxtType == "file"){
        $cStyle .= ";width:" . (($nWidthStyle*6) + 100) . "px;" ;
      }
    }

    $_c = ' ' . $cSize . $cMaxLength . $cChecked . $cReadOnly . $cClass . ' Style="' . $cStyle . '"' ;
    if($cTxtType == "hidden") $_c = "" ;

    $cDiv = "" ;
    $cClass = '' ;
    $cLabel = "" ;
    $cLabel1 = "" ;
    if($cTxtType == "checkbox" || $cTxtType == "radio"){
      $cDiv = '<div></div>' ;
      $cClass = ' class="input-check" ' ;
      $cLabel = "<label class='input-label' for='txt-" . self::$ID . "'>" ;
      $cLabel1 = "</label>" ;
    }else if($cTxtType == "file"){
			$cClass = ' class="input-file" ' ;
      $cLabel = "" ; //<label class='input-file-label' for='txt-" . self::$ID . "'> 📎 File </label>" ;
      $cLabel1 = "" ;
		}

    $cPlaceHolder = "" ;
    if(!empty(self::$PlaceHolder)) $cPlaceHolder = " placeholder='".self::$PlaceHolder."' " ;
		$cInput = ' <input ' . $cClass . 'id="txt-' . self::$ID . '" name="' . $cName . '" type="' . $cType . '" value="' . $cValue . '"' . $cPlaceHolder . $_c . '>' . $cDiv ;
    $cCaption = '' ;
    if(self::$Caption <> "") $cCaption = '<div class=\'input-caption no_txt_select\'> ' . str_replace(" ","&nbsp;",self::$Caption) . ' </div>' ;

    $cLCap = "" ;
    if(self::$LCaption <> ""){
      $cLCap = '<div name="Cap_' . $cName . '" class="input-lcaption" style="width:' . self::$LCapWidth . ';min-width:' . self::$LCapWidth . ';max-width:' . self::$LCapWidth . '">' . self::$LCaption . '</div>' ;			
      if(self::$LCapSemiColon){
        $cLCap .= '<div class="input-lcaption">:</div>' ;
      }
    }

		if(count(self::$required) <= 2) self::Required(false) ;
		$st = self::$required["char"] == "" || !self::$required["required"] ? "style='display: none;'" : "" ;

		// Daftar Event
		$vaEvent = [
			"onKeyDown"=>self::updPar(self::$onKeyDown),
			"onFocus"=>self::updPar(self::$onFocus),
			"onBlur"=>self::updPar(self::$onBlur),
			"onKeyPress"=>self::updPar(self::$onKeyPress),
			"onKeyUp"=>self::updPar(self::$onKeyUp),
			"onClick"=>self::updPar(self::$onClick),
			"onChange"=>self::updPar(self::$onChange),
			"onMouseOver"=>self::updPar(self::$onMouseOver),
		] ;
		
		// Kita Akan Menyimpan Configurasi Input pada span ini contoh untuk requred min,max,dataType dll
		$vaConf ['required'] = self::$required ;
		$vaConf ['required']["type"] = $cTxtType ;
		$vaConf ['input'] = ["defaultValue"=>$cValue,"type"=>$cTxtType,"caption"=>self::$Caption,"lcaption"=>self::$LCaption,
												"lcaptionWidth"=>self::$LCapWidth,"lcaptionSemiColon"=>self::$LCapSemiColon,"checked"=>self::$Checked,
												"button"=>self::$Button,"readOnly"=>$lReadOnly] ;
		$vaConf ["event"] = $vaEvent ;
		$cCaption .= "<div id='conf-" . self::$ID . "' style='display: none;'>" . json_encode($vaConf) . "</div>" ;
		
		// Tulis Kita siapkan div Container Kecuali jenis hidden
		$cDivContainer = "<div id='container-" . self::$ID . "' class='div-input-container'>" ;
		$cDivContainer2 = "</div>" ;
		if($cTxtType == "hidden"){
			$cDivContainer = "" ;
			$cDivContainer2 = "" ;
		}
    echo($cDivContainer . $cLabel . $cLCap . $cInput . $cButton . $cCaption . $cLabel1 . $cDivContainer2) ;

    self::Init() ;
  }
}