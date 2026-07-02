<?php
/* 
Standart Class Autoload
1. Pastikan File Berapa di Folder project/include/autoload/Classes/
2. Pastikan Nama Class Sama Dengan Nama File
3. Tidak Boleh Ada nama Class Kembar di semua Subdir nya
*/
class PrintDialog {
  static function Get(){
		$va = json_decode(aCfg::Get("cSession_PrintDialog"),true) ;

		$default = ["export"=>0,"paper"=>"LETTER (8.5 x 11) Inc.","pwidth"=>8.5,"pheight"=>11,'mtop'=>10,"mleft"=>13,"mbottom"=>10,"mright"=>7,"tab_baru"=>0] ;
		foreach($default as $key=>$value){
			if(!isset($va[$key])) $va[$key] = $value ;
		}
		return $va ;
	}

	static function Save($va){
		$cExportCSV = isset($va ['ckExportCSV']) ? 1 : 0 ;
		$cTabBaru = isset($va["ckTabBaru"]) ? 1 : 0 ;
		$va1 = [
			"export"=>$cExportCSV,
			"paper"=>$va["cPaper"],
			"pwidth"=>$va['nWidth'],
			"pheight"=>$va['nHeight'],
			'mtop'=>$va['nTop'],
			"mleft"=>$va['nLeft'],
			"mbottom"=>$va['nBottom'],
			"mright"=>$va['nRight'],
			"tab_baru"=>$cTabBaru
		] ;
		$cData = json_encode($va1);
		aCfg::Upd("cSession_PrintDialog",$cData) ;
	}
	
	static function GetPaperSize(){
		return ["A3 (11.69 x 16.54) Inc.","A4 (8.27 x 11.69) Inc.","FOLIO (8.5 x 13) Inc.","LEGAL (8.5 x 14) Inc.","LETTER (8.5 x 11) Inc."] ;
	}
}
