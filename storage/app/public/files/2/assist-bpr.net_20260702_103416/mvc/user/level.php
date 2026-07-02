
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
					<td height="" class="panel_main">
						<table width="100%"  border="0" cellspacing="0" cellpadding="1">
							<tr>
								<td width="100px">&nbsp;Level</td>
								<td width="5px">:</td>
								<td>
									<?php
	txt::$Button = true ;
	txt::Show("cKodeLevel","",4,4) ;
	txt::Show("cKeteranganLevel","",25,25,true) ;
									?>
								</td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td height="18px" class="panel_main">
						<table width="100%">
							<tr>
								<td align="right">
									<?php
									txt::ButtonField("cmdPreview","Preview") ;
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
