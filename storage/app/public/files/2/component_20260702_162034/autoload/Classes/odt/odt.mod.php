<?php
  include 'df.php' ;

class odt{
  private static $tmpDir = "" ;
  private static $outFile = "" ;
  private static $vaFile = array() ;
  private static $cExtension = "odt" ;
  private static $lZip = false ;
  private static $fileSeparator = "--file-separator--" ;

  public static function Open($cFile){
    self::$vaFile = array() ;
    self::$tmpDir = self::tmp() ;
    $vaFile = pathinfo($cFile) ;
    self::$lZip = true ;
    self::$cExtension = strtolower($vaFile ['extension']) ;
    if(self::$cExtension == "odt" || self::$cExtension == "ods"){
      self::$vaFile = array(self::$tmpDir . "/content.xml"=>0,self::$tmpDir . "/styles.xml"=>1) ;
    }else if(self::$cExtension == "docx"){
      self::$vaFile = array(self::$tmpDir . "/word/document.xml"=>0) ;
    }else if(self::$cExtension == "xlsx"){
      self::$vaFile = array(self::$tmpDir . "/xl/sharedStrings.xml"=>0) ;
    }else{
      self::$vaFile = array($cFile=>0) ;
      self::$lZip = false ;
    }

    // Untuk File Doc bukan berbasis xml jadi dia bukan merupakan file xml yang di zip.
    if(self::$lZip){
      $cContent = "" ;
      $zip = new ZipArchive;
      if ($zip->open($cFile) === TRUE) {
        $zip->extractTo(self::$tmpDir);
        $zip->close();
      }
    }

    $cContent = "" ;
    foreach(self::$vaFile as $key=>$value){
      if(is_file($key)){
        if($cContent <> "") $cContent .= self::$fileSeparator ;

        $file = fopen($key,"r");
        $size_of_file = filesize($key);
        $cContent .= fread($file, $size_of_file);
        fclose($file);
      }
    }
    return $cContent ;
  }
	
	public static function Save($content,$lPDF =false){    
    self::$outFile = "output_" . md5(rand(0,10000) . time() ) . "." . self::$cExtension ;
    if(self::$lZip){
      $zip = new ZipArchive();
      $zip->open(self::$tmpDir . "/" . self::$outFile,ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);
      self::CreateODT($content,self::$tmpDir,$zip) ;
      $zip->close();
    }else{
      $file = fopen(self::$tmpDir . "/" . self::$outFile, "w");
      fwrite($file,$content) ;
      fclose($file) ; 
    }
    $cFile = self::$tmpDir . "/" . self::$outFile ;
    if($lPDF){
      $cFile       = escapeshellarg($cFile);
      $cOutPuts     = escapeshellarg(self::$tmpDir."/") ;
      //$tmpProfile =  self::$tmpDir ."/libreoffice-profile/"; 
      $tmpProfile =  "/var/www/prg/app/tmp/libreoffice-profile/"; 
      if (!is_dir($tmpProfile)) {
        mkdir($tmpProfile, 0777, true);
      }
      $cmd = "/usr/bin/libreoffice --headless -env:UserInstallation=file://$tmpProfile --convert-to pdf --outdir $cOutPuts $cFile";
      exec($cmd . " 2>&1", $output, $return_var);
      return self::$tmpDir . "/" . str_replace(".".self::$cExtension,".pdf",self::$outFile);
    }
    return self::$tmpDir . "/" . self::$outFile ;
  }

  public static function br(){
    $cRetval = '' ;
    if(self::$cExtension == "docx"){
      $cRetval = "<w:p/>" ;
    }else if(self::$cExtension == "odt"){
      $cRetval = '<text:line-break/>' ;
    }
    return  $cRetval ;
  }

  // Private Function
  private static function CreateODT($content,$cDir,$zip){
    $vaContent = explode(self::$fileSeparator,$content) ;
    if(is_dir($cDir)){
      $d = dir($cDir) ;            
      while (false !== ($entry = $d->read())) {
        if(is_dir($cDir . '/' . $entry)){
          if($entry !== "." && $entry !== ".."){
            self::CreateODT($content,$cDir . '/' . $entry,$zip) ;
          }
        }else{
          $cFileToZip = str_replace(self::$tmpDir . "/","",$cDir. "/" . $entry) ;
          if(is_file($cDir . '/' . $entry) && $entry <> self::$outFile){
            $cF = $cDir . "/" . $entry ;
            if(isset(self::$vaFile [$cF])){
              $nc = self::$vaFile [$cF] ;
              if(isset($vaContent [$nc])){
                $zip->addFromString($cFileToZip,$vaContent [$nc]);
              }
            }else{
              $zip->addFile($cDir . '/' . $entry,$cFileToZip);
            }
          }
        }
      }
      $d->close();
    }
  }
  
  private static function tmp(){  
    $cDir = "../tmp" ;
    if(!is_dir($cDir)) mkdir($cDir,0777);
  
    $cDir = "../tmp/tmp" ;  
    $nDir = date("H")%3 ;
    $nDir1 = $nDir + 1 ;
    if($nDir1 == 3) $nDir1 = 0 ;

    if(is_dir($cDir . $nDir1)) self::DelDir($cDir . $nDir1);
    if(!is_dir($cDir . $nDir)) mkdir($cDir . $nDir,0777);
    
    $cDir .= $nDir . "/w_" . md5(rand(0,10000) . time()) ;
    if(!is_dir($cDir)) mkdir($cDir,0777)  ;
    return  $cDir ;
  }

  private static function DelDir($cDir){
    if(is_dir($cDir)){
      $d = dir($cDir) ;            
      while (false !== ($entry = $d->read())) {
        if(is_dir($cDir . '/' . $entry)){
          if($entry !== "." && $entry !== ".."){
            self::DelDir($cDir . '/' . $entry) ;
          }
        }else{
          if(is_file($cDir . '/' . $entry)){
            unlink($cDir . '/' . $entry) ;
          }
        }
      }
      $d->close();
      rmdir($cDir) ;
    }
  }
	
  public static function space($nTotal=1){
	  $cRetval = "" ;
	  for($n=1;$n<=$nTotal;$n++){
		  $cRetval .= "<text:s/>" ;
	  }
	  return $cRetval ;
  }

  public static function tab($nTotal=1){
	  $cRetval = "" ;
	  for($n=1;$n<=$nTotal;$n++){
		  $cRetval .= "<text:tab/>" ;
	  }
	  return $cRetval ;
  }
}