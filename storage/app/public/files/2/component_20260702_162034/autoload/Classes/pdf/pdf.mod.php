<?php
class pdf{
	private static $pdf = null ;

	static function Init($paper='LETTER',$orientation='portrait',$pagenumber='1',$nPaperIncWidth=0,$nPaperIncHeight=0){
		return self::$pdf = new Cezpdf($paper,$orientation,$pagenumber,$nPaperIncWidth,$nPaperIncHeight) ;
	}

	static function PageHeader($cText,$vaOption=array()){
		return self::$pdf->ezPageHeader($cText,$vaOption) ;
	}
	
	static function Table(&$data,$cols='',$title='',$options=array()){
		return self::$pdf->ezTable($data,$cols,$title,$options) ;
	}
	
	static function Stream($option=array()){
		return self::$pdf->ezStream($option) ;
	}
	
	static function SetCmMargins($top,$bottom,$left,$right){
		return self::$pdf->ezSetCmMargins($top,$bottom,$left,$right) ;
	}

	static function ColumnsStart($options=array()){
		return self::$pdf->ezColumnsStart($options) ;
	}

	static function ColumnsStop(){
		return self::$pdf->ezColumnsStop() ;
	}

	static function InsertMode($status=1,$pageNum=1,$pos='before'){
		return self::$pdf->ezInsertMode($status,$pageNum,$pos) ;
	}

	static function NewPage(){
		return self::$pdf->ezNewPage() ;
	}

	static function SetMargins($top,$bottom,$left,$right){
		return self::$pdf->ezSetMargins($top,$bottom,$left,$right) ;
	}  

	static function GetCurrentPageNumber(){
		return self::$pdf->ezGetCurrentPageNumber() ;
	}

	static function StartPageNumbers($x,$y,$size,$pos='left',$pattern='{PAGENUM} of {TOTALPAGENUM}',$num=''){
		return self::$pdf->ezStartPageNumbers($x,$y,$size,$pos,$pattern,$num) ;
	}

	static function Text($text,$size=0,$options=array(),$test=0){
		return self::$pdf->ezText($text,$size,$options,$test) ;
	}
	
	static function Image($image,$pad = 5,$width = 0,$resize = 'full',$just = 'center',$border = ''){
		return self::$pdf->ezImage($image,$pad,$width,$resize,$just,$border) ;
	}

	static function getText($cValue){
		return self::$pdf->getText($cValue) ;
	}
	
	static function Array2CSV($vaArray,$lShowHeader=true){
		return self::$pdf->Array2CSV($vaArray,$lShowHeader) ;
	}

	static function CreateFileExport($cExtention = '.csv'){
		return self::$pdf->CreateFileExport($cExtention) ;
	}
	
	static function HeaderInfo($orientation){
		return self::$pdf->ezHeaderInfo($orientation) ;
	} 
	
	static function PageNumber(){
		return self::$pdf->ezSisPageNumber() ;
	}

	static function InfoBank(){
		return self::$pdf->InfoBank() ;
	}
	
	static function openObject(){
		return self::$pdf->openObject() ;
	}
	
	static function closeObject(){
		return self::$pdf->closeObject() ;
	}
	
	static function addObject($textObjectId){
		return self::$pdf->addObject($textObjectId) ;
	}
}