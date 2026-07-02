<script language="javascript" type="text/javascript">

function Form_onLoad(){
  if("<?php echo(GetSetting("cSession_UserLevel")) ?>" !== "0000"){
    a.delObj(a.f.cmdPreview) ;
  	a.alert("Anda Tidak Memiliki Hak Menjalankan Menu ini, Hanya Group User 0000 yang bisa menjalankan Menu ini ...") ;
  }
}

function cmdPreview_onClick(field){
  rpt.open();
}

function cKodeLevel_onButtonClick(field){
	field.Browse("SeekLevel","cKode="+field.value,{"Keterangan":a.f.cKeteranganLevel},true);
}
</script>