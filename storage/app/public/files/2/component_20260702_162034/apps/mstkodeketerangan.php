<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Assistindo.Net</title>
</head>
<?php MVC::LoadComponent() ?>
<?php include GetFileModul(__FILE__,'.jscript.php') ?>
<body>
<form name="form1">
<table width="100%" height="100%" border="0" cellspacing="3" cellpadding="0" class="cell_eventrow">
	<tr>
		<td height="20px"	class="panel_main">
			<table width="100%"	border="0" cellspacing="0" cellpadding="1">
				<tr>
					<td width="100px">&nbsp;Kode</td>
					<td width="5px">:</td>
					<td>
					<?php
						txt::Required(true,"*") ;
						txt::Show("cKode","","4","4") ;
					?>
					</td>
				</tr>
				<tr>
					<td width="100px">&nbsp;Keterangan</td>
					<td width="5px">:</td>
					<td>
					<?php
						txt::Required() ;
						txt::Show("cKeterangan","","40","40") ; 
					?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td class="panel_main">
		<!-- your grid here -->
		<?php
			dbg::$AddColumn = array("Kode","Keterangan");

			dbg::$Height = "100%";
			dbg::$Col ["Kode"] = ["Width"=>60,"Align"=>"center"] ;
			dbg::$Col ["Keterangan"] = ["Width"=>390] ;
			dbg::dataBind() ;
		?>
		</td>
	</tr>
	<tr>
		<td height="18px" class="panel_main">
			<table width="100%">
				<tr>
					<td>
					<?php
						txt::$onClick="getEdit(true,1)" ;
						txt::ButtonField("cmdAdd","Add") ;

						txt::$onClick="getEdit(true,2)" ;
						txt::ButtonField("cmdEdit","Edit") ;

						txt::$onClick="getEdit(true,3)" ;
						txt::ButtonField("cmdDelete","Delete") ;
					?>
					</td>
					<td align="right">
					<?php
						txt::HiddenField("nPos","0") ;
						txt::HiddenField("cTableName",Svr::GetPar("name","",false)) ;
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
