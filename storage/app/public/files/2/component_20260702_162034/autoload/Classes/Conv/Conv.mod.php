<?php
  include 'df.php' ;

class Conv {
	static function String2Number($cString){
		return (float) str_replace(",","",$cString) ;
	}

	static function Number2String($nNumber,$nDecimals=2){
		$nNumber = floatval(String2Number($nNumber)) ;
		return number_format($nNumber,$nDecimals,".",",") ;
	}

	static function NextMonth($nTime,$nNextMonth){
		$nDay = date("d",$nTime) ;
		$nMonth = date("m",$nTime) ;
		$nYear = date("Y",$nTime) ;

		$n1 = mktime(0,0,0,$nMonth + $nNextMonth,$nDay,$nYear) ;
		$n2 = mktime(0,0,0,$nMonth+$nNextMonth+1,0,$nYear) ;
		return min($n1,$n2) ;
	}

	static function NextDay($nTime,$nNextDay){
		$nDay = date("d",$nTime) ;
		$nMonth = date("m",$nTime) ;
		$nYear = date("Y",$nTime) ;

		$n = mktime(0,0,0,$nMonth,$nDay+$nNextDay,$nYear) ;
		return $n ;
	}

	static function NextWeek($nTime,$nNextWeek){
		return NextDay($nTime,$nNextWeek*7) ;
	}

	static function Date2String($dTgl){
		$cRetval = substr($dTgl,0,10) ;
		$va = explode("-",$dTgl) ;
		// Jika Array 1 Bukan Tahun maka akan berisi 2 Digit
		if(strlen($va [0]) == 2){
			$cRetval = $va [2] . "-" . $va [1] . "-" . $va[0] ;
		}
		return $cRetval ;
	}

	static function String2Date($cString){
		$cRetval = substr($cString,0,10) ;
		$va = explode("-",$cString) ;
		// Jika Array 1 Tahun maka akan berisi 4 Digit
		if(strlen($va [0]) == 4){
			$cRetval = $va [2] . "-" . $va [1] . "-" . $va[0] ;
		}
		return $cRetval ;
	}

	static function Devide($a,$b){
		$nRetval = 0 ;
		if(empty($a) || empty($b) || $a == 0 || $b == 0){
			$nRetval = 0 ;
		}else{
			$nRetval = $a / $b ;
		}
		return $nRetval ;
	}

	static function GetFileModul($cFileName,$cExt){
		$cDir = dirname($cFileName) ;
		$vaFile = explode('.',basename($cFileName)) ;
		$cFile = $vaFile [0] . $cExt ;
		// Untuk Load jscript system mvc dari awal kita sudah load source yang sudah di compress
		// untuk membiasakan rapi dalam pembuatan Source Javascript
		if($cExt == ".jscript.php"){
			if(Svr::IsMVC()){
				$cDir .= "/" . $cFile ;
				if(is_file($cDir)){
					$cFile =  CompressScript::jscript($cDir) ;
				}
			}else{
				$cFile = $cDir . "/" . $cFile ;
			}
		}
		return $cFile ;
	}
	
	static function GetVar($va=[],$lGet=true,$lPost=true){
		$cRetval = "" ;
		if($lGet && !empty($_GET)){
			foreach($_GET as $key=>$value){
				$cRetval .= "\$" . $key . " = \$_GET['" . $key . "'] ;" ;
			}
		}

		if($lPost && !empty($_POST)){
			foreach($_POST as $key=>$value){
				$cRetval .= "\$" . $key . " = \$_POST['" . $key . "'] ;" ;
			}
		}

		// Kalau kita mau mendefinikikan $va kita tidak tahu varible sumber apa, kalau berbeda akan error
		// Solusinya kita titipkan di $_GET dengan membuat key _OWNVAR_ setelah selesai nanti kita hapus variable itu.
		if(!empty($va)){
			$_GET['_OWNVAR_'] = $va ;
			foreach($va as $key=>$value){
				$cRetval .= "\$" . $key . " = \$_GET['_OWNVAR_']['" . $key . "'] ;" ;
			}			
			$cRetval .= "unset(\$_GET['_OWNVAR_']) ;" ;
		}

		return $cRetval ;
	}
}