<html>
<head>
<title>Component - PT. Assist Software Indonesia Pratama</title>
</head>
<body>
<div style="FONT-FAMILY: Verdana,Helvetica; FONT-SIZE: 12px">
<?php
  $cFile = "./index.txt" ;
  if(is_file($cFile)){
    $vaFile = file($cFile) ;
    foreach($vaFile as $key=>$value){
      echo( str_replace(" ","&nbsp;",$value) . "<br>") ;
    }
  }else{
    echo("Tidak ada..") ;
  }
?>
</div>
</body>
</html>