<?php include 'df.php' ; ?>
<script language="javascript" type="text/javascript">
var oDisp = null ;
var cFolder = "" ;
function DBGRID1_onClick(vaRow,nCol){
	let url = compFolder() + "/app/frmchangethemes.disp.php?css=" + vaRow [2] + "&compFolder=" + compFolder() ;
  if(oDisp == null) oDisp = a.getById("frmDisplay") ;
	oDisp.src = url ;

  cFolder = vaRow [2] ;
}

function cmdApply_onClick(){
  a.confirm("Tema disimpan ?","Confirm",function(par){
    if(par){
			let url = "" ;
			let method = 'ApplyThemes()' ;
			if(svr.IsMVC()){
				url = svr.GetBaseURL() + "component/app/frmchangethemes.ajax.php" ;
				method = "" ;
			}
      a.ajax(url,method,"cFolder=" + cFolder+"&optAnimasi="+a.f.optAnimasi.value,function(cData,nStatus){
        if(cData == "ok"){
          a.confirm("Untuk mengaktifkan Themes ini anda harus refresh Halaman. \n Anda ingin Refreh halaman ini ?","Confirm",function(par){
            if(par){
              var win = self.parent ;
              var n = 0 ;
              while(win.name !== "mainFrame" && n < 10){
                n ++ ;
                win = win.self.parent ;
              }
              win.self.parent.location.reload() ;
            }
          }) ;
        }else{
          a.alert(cData) ;
        }
      }) ;
    }
  }) ;
}

function cmdApplyAnimasi_onClick(){
	alert('oke') ;
}
</script>