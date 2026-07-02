<?php
  include 'df.php' ;
  $__cNoAnimation = 0 ;
	if(Svr::IsMVC()){
		$__cNoAnimation = GetSetting("cSession_Themes_NoAnimation",0) ;
	}else{
		$__cDirConfig = Dir::DataDir(".themes-config") ;
		$_cssfn = $__cDirConfig . "/" . md5(GetSetting("cSession_UserName")) ;
		if(is_file($_cssfn)){
			$__cDir = trim(file_get_contents($_cssfn));
			$vaDir = explode('|', $__cDir); 
			if(isset($vaDir[1])) $__cNoAnimation = $vaDir[1];
		}
	}
	$optAnimasiY = ($__cNoAnimation) ? true : false ;
  $optAnimasiT = (!$__cNoAnimation) ? true : false;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Assistindo.Net</title>
</head>
<?php if(Svr::IsMVC()) MVC::LoadComponent() ?>
<?php include GetFileModul(__FILE__,'.jscript.php') ?>
<body>
<form name="form1" method="post">
<table width="100%" height="100%" border="0" cellspacing="3" cellpadding="0" class="cell_eventrow">
  <tr>
    <td style="border:1px solid #999999;padding:4px">
      <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="1">
        <tr>
          <td width="300px" >
          <?php
						$data = [
							["No"=>1,"Name"=>"Default","Folder"=>"default"],
	            ["No"=>2,"Name"=>"Aero Lite","Folder"=>"aero"],
							["No"=>3,"Name"=>"Modern Aero","Folder"=>"win10"],
							["No"=>4,"Name"=>"Radiant Red","Folder"=>"merah"],
							["No"=>5,"Name"=>"Cool Green","Folder"=>"hijauui"],
							["No"=>6,"Name"=>"Cool Orange","Folder"=>"jinggaui"],
							["No"=>7,"Name"=>"Modern Blue","Folder"=>"biruui"],
							["No"=>8,"Name"=>"Radiant Mazarine","Folder"=>"nilaui"],
							["No"=>9,"Name"=>"Radiant Magenta","Folder"=>"purple"],
							["No"=>10,"Name"=>"Black Mate","Folder"=>"blackui"],
							["No"=>11,"Name"=>"Black Night","Folder"=>"night"]
						] ;
            dbg::$Array = $data ;

            dbg::$Height = "100%";
            dbg::$Col ['No'] = ["Align"=>"center","Width"=>40] ;
            dbg::$Col ['Name'] = ["Width"=>250] ;
            dbg::$Col ['Folder'] = ["Display"=>"hidden"] ;
            dbg::$Scrolling = "vertical" ;
            dbg::dataBind() ;
          ?>
          </td>
          <td width="2px" nowrap rowspan='2'></td>
          <td rowspan='2'>
          <iframe width="100%" height="100%" style="border:1px solid #999999" id="frmDisplay"></iframe>
          </td>
        </tr>
				<tr>
					<td height="20px" style="border:1px solid #999999;padding:4px">Animasi Tema :
						<?php
							txt::RadioButton("optAnimasi","1",'','Tanpa Animasi',$optAnimasiY) ;
							txt::RadioButton("optAnimasi","0",'','Dengan Animasi',$optAnimasiT) ;
						?>
					</td>
				</tr>
      </table>
    </td>
  </tr>
  <tr>
    <td height="20px" style="border:1px solid #999999">
      <table width="100%" style="padding:2px">
        <tr>
          <td align="right">
          <?php
            txt::ButtonField("cmdApply","Apply") ;

            txt::$onClick = "frm.close();" ;
            txt::ButtonField("cmdCancel","Close") ;
          ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

</form>
</body>
</html>