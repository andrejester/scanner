<?php include 'df.php' ; ?>
<script language="javascript" type="text/javascript">
function MnuLogOut_onClick(){
	a.confirm("Anda Akan Logout ?","",function(par){
		if(par){   
			a.ajax("","logout","",function(cData,status){
				open(__BASE_URL__,"_parent") ;
			}) ;
		}
	}) ;
}

function MnuChangeThemes_onClick(){
	frm.open("/component/app/frmchangethemes.php","FrmChangeThemes","Change Themes",1000,640,'',false,'no',false,'mainFrame') ; 
}
</script>