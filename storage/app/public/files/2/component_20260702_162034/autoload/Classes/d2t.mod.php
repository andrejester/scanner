<?php
  include 'df.php' ;

class d2t {
  static function Dec2Text($nDec,$lRupiah=true,$nRound=2){
    $nDec = number_format($nDec,$nRound,'.','') ;
		$vaDec = explode(".",$nDec) ;
    $cRetval = "" ;
    if($vaDec [0] < 11){
      $cRetval .= self::Satuan($vaDec [0]) ;
    }else if($vaDec [0] <= 99){
      $cRetval .= self::Puluan($vaDec [0]) ;
    }else if($vaDec [0] <= 999){
      $cRetval .= self::Ratusan($vaDec [0]) ;
    }else if($vaDec [0] <= 999999){
      $cRetval .= self::Ribuan($vaDec [0]) ;
    }else if($vaDec [0] <= 999999999){
      $cRetval .= self::Jutaan($vaDec [0]) ;
    }else if($vaDec [0] <= 999999999999){
      $cRetval .= self::Milyard($vaDec [0]) ;
    }else if($vaDec [0] <= 999999999999999){
      $cRetval .= self::Trilyon($vaDec [0]) ;
    }else if($vaDec [0] <= 999999999999999999){
      $cRetval .= self::Kuadriliun($vaDec [0]) ;
    }else if($vaDec [0] <= 999999999999999999999){
      $cRetval .= self::Kuintiliun($vaDec [0]) ;
    }

    if(isset($vaDec [1]) && floatval($vaDec [1]) > 0){
      $cRetval .= "Koma " . self::Dec2Text($vaDec [1],false) ;
    }
    if($lRupiah) $cRetval .= "Rupiah " ;

    return $cRetval ;
  }
  
  private static function Satuan($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){
      $vaSatuan = array("Satu", "Dua", "Tiga", "Empat", "Lima","Enam", "Tujuh", "Delapan", "Sembilan",
                        "Sepuluh", "Sebelas") ;
      $cRetval = $vaSatuan [$nDec-1] . " " ;
    }
    return $cRetval ;
  }
  
  private static function Puluan($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){
      if($nDec <= 11){
        $cRetval .= self::Satuan($nDec) ;
      }else if($nDec <= 19){
        $cRetval .= self::Satuan(substr($nDec,1,1)) . "Belas " ;
      }else if($nDec <= 99){
        $cRetval .= self::Satuan(substr($nDec,0,1)) . "Puluh " ;
        $cRetval .= self::Satuan(substr($nDec,1,1)) ;
      }
    }
    return $cRetval ;
  }

  private static function Ratusan($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){  
      if($nDec <= 99){
        $cRetval .= self::Puluan($nDec) ;
      }else if($nDec <= 199){
        $cRetval .= "Seratus " . self::Puluan(substr($nDec,1)) ;
      }else if($nDec <= 999){
        $cRetval = self::Satuan(substr($nDec,0,1)) . "Ratus " . self::Puluan(substr($nDec,1)) ;
      }
    }
    return $cRetval ;
  }

  private static function Ribuan($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){
      if($nDec <= 999){
        $cRetval .= self::Ratusan($nDec) ;
      }else if($nDec <= 1999){
        $cRetval .= "Seribu " . self::Ratusan(substr($nDec,1)) ;
      }else if($nDec <= 999999){
        $cDecimal = str_pad($nDec,6,"0",STR_PAD_LEFT) ;
        $cRetval .= self::Ratusan(substr($cDecimal,0,3)) . "Ribu " . self::Ratusan(substr($cDecimal,3)) ;
      }
    }
    return $cRetval ;
  }

  private static function Jutaan($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){
      if($nDec <= 999999){
        $cRetval .= self::Ribuan($nDec) ;
      }else if($nDec <= 999999999){
        $cDecimal = str_pad($nDec,9,"0",STR_PAD_LEFT) ;
        $cRetval .= self::Ratusan(substr($cDecimal,0,3)) . "Juta " ;
        $cRetval .= self::Ribuan(substr($cDecimal,3)) ;
      }
    }
    return $cRetval ;
  }

  private static function Milyard($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){
      if($nDec <= 999999999){
        $cRetval .= self::Jutaan($nDec) ;
      }else if($nDec <= 999999999999){
        $cDecimal = str_pad($nDec,12,"0",STR_PAD_LEFT) ;
        $cRetval .= self::Ratusan(substr($cDecimal,0,3)) . "Miliar " ;
        $cRetval .= self::Jutaan(substr($cDecimal,3)) ;
      }
    }
    return $cRetval ;
  }

  private static function Trilyon($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){
      if($nDec <= 999999999999){
        $cRetval .= self::Milyard($nDec) ;
      }else if($nDec <= 999999999999999){
        $cDecimal = str_pad($nDec,15,"0",STR_PAD_LEFT) ;
        $cRetval .= self::Ratusan(substr($cDecimal,0,3)) . "Triliun " ;
        $cRetval .= self::Milyard(substr($cDecimal,3)) ;
      }
    }
    return $cRetval ;
  }
  
  private static function Kuadriliun($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){
      if($nDec <= 999999999999999){
        $cRetval .= self::Trilyon($nDec) ;
      }else if($nDec <= 999999999999999999){
        $cDecimal = str_pad($nDec,18,"0",STR_PAD_LEFT) ;
        $cRetval .= self::Ratusan(substr($cDecimal,0,3)) . "Kuadriliun " ;
        $cRetval .= self::Trilyon(substr($cDecimal,3)) ;
      }
    }
    return $cRetval ;
  }
  
  private static function Kuintiliun($nDec){
    $cRetval = "" ;
    $nDec = number_format($nDec,0,'','') ;
    if($nDec > 0){
      if($nDec <= 999999999999999999){
        $cRetval .= self::Kuadriliun($nDec) ;
      }else if($nDec <= 999999999999999999999){
        $cDecimal = str_pad($nDec,21,"0",STR_PAD_LEFT) ;
        $cRetval .= self::Ratusan(substr($cDecimal,0,3)) . "Kuadriliun " ;
        $cRetval .= self::Kuadriliun(substr($cDecimal,3)) ;
      }
    }
    return $cRetval ;
  }
}