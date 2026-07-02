<?php include 'df.php' ; ?>
<script language="javascript" type="text/javascript">
function cmdPreview_onClick(field){
	if(frm.isValidSaving()){
		a.ajax('','SaveConfig()',a.fContent(),(obj)=>{
			Preview() ;
		}) ;
	} 
}

function Preview(){
	frm.callFunc("rpt.openReport",[a.f.ckTabBaru.checked],"mainFrame") ;
  CloseForm() ;
}

function Form_onLoad(){
  a.f.cmdPreview.focus() ;
	PaperSize(a.f.cPaper) ;
}

function PaperSize(field){
	let value = field.value ;
	value = value.split('(')[1] ;
	value = value.replaceAll("'","") ;
	value = value.replaceAll(")","") ;
	value = value.replaceAll("Inc.","") ;
	value = value.split("x") ;
	for(let n in value){
		value[n] = parseFloat(value[n]) ;
	}
	if(value.length >= 2){
		a.f.nWidth.value = value[0] ;
		a.f.nHeight.value = value[1] ;
	}	
}

function SetCustom(){
  with(document.form1){
    nWidth.readOnly = false ;
    nHeight.readOnly = false ;
    
    fieldfocus(nWidth) ;
  }
}

function ckDefault_onClick(field){
	if(field.checked){
		a.f.cPaper.value = "LETTER (8.5 x 11) Inc." ;
		PaperSize(a.f.cPaper) ;
		
		a.f.nTop.value2 = 10 ;
		a.f.nBottom.value2 = 10 ;
		a.f.nLeft.value2 = 13 ;
		a.f.nRight.value2 = 7 ;
		a.f.ckTabBaru.checked = false ;
	}
}

function FieldFormat(field){
  field.value = String2Number(field.value) ;
  field.value = Number2String(field.value,2) ;
}
</script>