<?php
	$cFile = isset($_GET["css"]) ? $_GET["css"] : "" ;
	$cFile = $cFile == "" && isset($_POST["css"]) ? $_POST["css"] : $cFile ;

	$cDir = isset($_GET["compFolder"]) ? $_GET["compFolder"] : "" ;
	$cDir = $cDir == "" && isset($_POST["compFolder"]) ? $_POST["compFolder"] : $cDir ;

	$css = "../themes/css.php?t=" . $cFile ;
  $compFolder = $cDir ;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Assistindo.Net</title>
<link rel="stylesheet" type="text/css" href="<?php echo($css) ?>"> 
</head>
<body>
<!-- Toolbar -->
<div style="display:block;position:absolute;top:5;left:20;width:600">
  Toolbar
  <div class="tbar_main" style="height:23px">
    <div class="tbar_main" id="ssTolbar1" style="top: 0px; left: 0px; height: 24px; width: 100%;"><div class="tbar_item_div" id="Close-toolBar-div">
    </div>
  </div>
</div>

<div style="display:block;position:absolute;top:50px;left:20px;width:600">
  Status Bar
  <div class="sbar_main" style="border-bottom:1px solid #dddddd">
    <table width="100%"  border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="sbar_item">Item 1</td>
        <td class="sbar_item">Item 2</td>
      </tr>
    </table>
  </div>
</div>

<!-- Form Focus -->
<div style="display:block;position:absolute;top:100px;left:20;width:300;height:150px">
  <div class="win_main win_main_custom fadein" style="width:100%;height:100%">
    <div class="win_header win_header_focus">
      <div class="win_title">Form Focus</div>
      <div class="win_icon_close">✕</div>
      <div class="win_icon_min">&mdash;</div>
			<div class="win_icon_panduan" style="display:block">?</div>
    </div>
    <div class="win_border" style="top:29px;height:100px;width:298px"></div>
  </div>
</div>

<!-- Form Blur -->
<div style="display:block;position:absolute;top:100px;left:330;width:300;height:150px">
  <div class="win_main win_main_custom" style="width:100%;height:100%">
    <div class="win_header win_header_blur">
      <div class="win_title">Form Blur</div>
      <div class="win_icon_close">✕</div>
      <div class="win_icon_min">&mdash;</div>
			<div class="win_icon_panduan" style="display:block">?</div> 
    </div>
    <div class="win_border" style="top:29px;height:100px;width:298px"></div>
  </div>
</div>

<!-- Alert -->
<div style="display:block;position:absolute;top:235px;left:20px;width:300;height:150">
  <div class="alr_main" style="width:100%">
    <div class="alr_head">
      <div class="alr_title">Alert</div>
    </div>
    <div class="alr_msg">Body Message</div>
    <div class="alr_button" align="center"><input type="Button" class="Button" name="cc" value="OK"></div>
  </div>
</div>

<!-- Confirm -->
<div style="display:block;position:absolute;top:235px;left:330px;width:300;height:150">
  <div class="alr_main" style="width:100%">
    <div class="alr_head">
      <div class="alr_title">Confirm</div>
    </div>
    <div class="alr_msg">Body Message</div>
    <div class="alr_button" align="center"><input type="Button" class="Button" value="OK">&nbsp;<input type="Button" class="Button" value="Cancel"></div>
  </div>
</div>

<!-- Menu Vertical -->
<div style="display:block;position:absolute;top:380px;left:20px;width:300;height:150">
  Vertical Menu
  <div class="menu_vert_main fadein" style="display:block">
  <table class="menu_vert_content" width="100px" border="0" cellspacing="0" cellpadding="0">
    <tr class="menu_vert_item"><td align="center" nowrap=""></td><td nowrap="">1.1&nbsp;&nbsp;Menu Item 1</td><td nowrap=""></td></tr>
    <tr class="menu_vert_item menu_vert_item_over"><td align="center" nowrap=""></td><td nowrap="">1.2&nbsp;&nbsp;Menu Item 2</td><td nowrap=""></td></tr>
    <tr class="menu_vert_item"><td align="center" nowrap=""></td><td nowrap="">1.3&nbsp;&nbsp;Menu Item 3</td><td nowrap=""></td></tr>
  </table>
  </div>
</div>
<!-- Tab -->
<div style="display:block;position:absolute;top:380px;left:180px;width:300;height:150">
  Tab
  <div class="tab_tab">
    <div class="tab_item tab_click no_txt_select">Nama</div>
    <div class="tab_item tab_normal no_txt_select" >Alamat</div>
    <div class="tab_item tab_normal no_txt_select" >Kota</div>
  </div>
 <div class="tab_body" >
  <div  class="tab_content" style="display: block; height: 60px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="1">
      <tbody><tr>
      <td width="150px">&nbsp;Kode / CIF Bank</td>
      <td width="5px">:</td>
      <td> 
        <span style="height:20px"><input placeholder="" name="cCabang" type="Text" value="01" size="2" maxlength="2" style=";width:32px;"><img src="/component/themes/default/images/pick-button.gif" onmouseout="this.src='/component/themes/default/images/pick-button.gif'" onmouseover="this.src='/component/themes/default/images/pick-button-over.gif'" border="0px" style="cursor:default" align="top"></span><span style="height:20px"> <input style=";width:60px;" ></span>
			</td>
			</tr></tbody>
     </table>
  </div>
 </div>
</div>
<!-- TaskBar -->
<div style="position:absolute;top:500px;left:20px;">
Menu TaskBar
<div class="menu_hrz_main">
<table id="table-menu-horizontal" width="10px" border="0" cellspacing="2" cellpadding="2">
 <tbody>
  <tr>
    <td id="cell-__cMenuID-1" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;1 File&nbsp;</td>
    <td id="cell-__cMenuID-2" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;2 Transaksi&nbsp;</td>
    <td id="cell-__cMenuID-3" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;3 Laporan&nbsp;</td>
    <td id="cell-__cMenuID-4" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;4 Laporan Gabungan&nbsp;</td>
    <td id="cell-__cMenuID-5" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;5 Utility&nbsp;</td>
    <td id="cell-__cMenuID-6" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;6 Setup&nbsp;</td>
    <td id="cell-__cMenuID-7" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;7 Admin&nbsp;</td>
    <td id="cell-__cMenuID-8" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;8 Help&nbsp;</td>
    <td id="cell-__cMenuID-9" nowrap="" class="menu_hrz_out no_txt_select">&nbsp;9 Installasi&nbsp;</td>
  </tr>
 </tbody>
</table>
</div>
</div>
</body>
</html>