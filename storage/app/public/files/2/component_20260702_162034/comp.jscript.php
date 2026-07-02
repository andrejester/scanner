<?php include 'df.php' ; ?>
<script language="javascript" type="text/javascript">
function CheckBody(){
  if(window.name == "mainFrame"){
    mainFrame_Body() ;
  }
}

function mainFrame_Body(){
var __ob = document.body ;
  if(__ob !== null){
    if(__ob.offsetWidth <= 1240){
      cFile = "<?php echo($vaBG['mainbgmin']) ?>" ;
    }else{  
      cFile = "<?php echo($vaBG['mainbgmax']) ?>" ;    
    }           
    __ob.background="./wallpaper/"+cFile ;  
  }else{  
    setTimeout(mainFrame_Body,500) ;
  }
}

// Untuk Pengecekan agak Session Tidak Habis kalau Form tidak di Rubah Lebih dari 5 Menit
// Pengecekan Setiap 2 menit sekali
function __checkSession(par){
  if(!par) a.ajax("../component/comp.main.ajax.php","CheckSession()") ;
  setTimeout(__checkSession,120000) ;
}

// Panggil Function CheckBody() ;
CheckBody() ;
</script>