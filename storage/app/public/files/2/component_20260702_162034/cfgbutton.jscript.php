<?php include 'df.php' ; ?>
<script language="javascript" type="text/javascript">
function Form_onLoad(){
  fieldfocus(a.f.cLevel) ;
  if(a.f.cLevel.value !== "") cLevel_onBlur(a.f.cLevel) ;
}

function cmdSave_onClick(field){
  a.confirm("Data Disimpan ?","",function(lPar){
    if(lPar){
      a.ajax('','SaveUserLevel()',a.fContent(),function(cInfo){
        a.alert(cInfo,"",function(){
          CloseForm() ;
        }) ;
      }) ;
    }
  }) ;
}

function cLevel_onBlur(field){
  a.ajax('','SeekUserLevel()',a.fContent([field,a.f.cFormName])) ;
}

function cLevel_onButtonClick(field){ 
  var cSQL = "Select Kode,Keterangan from username_level where Kode <> '0000' Order by Kode" ; 
  a.Browse(cSQL,field,function(vaRow){
    if(vaRow !== null){ 
      a.f.cKeterangan.value = vaRow [1] ;
      cLevel_onBlur(a.f.cLevel) ;
    }
  }) ;
}

function cmdCancel_onClick(field){
  CloseForm() ; 
}
</script>