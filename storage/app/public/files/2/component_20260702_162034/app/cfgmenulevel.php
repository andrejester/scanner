<?php
  include 'df.php' ;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Assistindo.Net</title>
</head>
<?php include GetFileModul(__FILE__,'.jscript.php') ?>
<style>
<?php
	require_once CompressScript::css( __DIR__ . "/cfgmenulevel.css") ;
?>
</style>
<body>
<form name="form1">
<table width="100%" height="100%" border="0" cellspacing="3" cellpadding="0" class="cell_eventrow" style="able-layout:fixed;">
	<tr>
		<td height="20px"	class="panel_main">
			<table width="100%"	border="0" cellspacing="0" cellpadding="1">
				<tr>
					<td width="100px">&nbsp;User Level</td>
					<td width="5px">:</td>
					<td>
					<?php
						txt::Required(true,"",4,4) ;
						txt::Show("cLevel","","4","4","",true) ;
	
						txt::Required(true) ;
						txt::Show("cKeteranganLevel","",40,40,true) ;
					?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="panel_main">
			<div id="divTree" style="display:none">
				<?php
					$subMenu = Svr::IsMVC() ? SisConfig::GetValue("mnuSubMenu") : "" ;
					menu::SubMenuFile($subMenu) ;
					$vaMenu = menu::mnu2Array() ;
					echo(json_encode($vaMenu))
				?>
			</div>
		</td>
	</tr>
	<tr>
		<td height="18px" class="panel_main">
			<table width="100%">
				<tr>
					<td align="right">
					<?php
						txt::HiddenField("cMenu","") ;

						txt::ButtonField("cmdSave","Save") ;
						txt::ButtonField("cmdCancel","Cancel") ;
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
