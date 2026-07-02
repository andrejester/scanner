<?php
  include 'df.php' ;
	$data = PrintDialog::Get() ;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Assistindo.Net</title>
</head>
<?php include GetFileModul(__FILE__,'.jscript.php') ?>
<body>
<form name="form1">
<table width="100%" height="100%" border="0" cellspacing="3" cellpadding="0" class="cell_eventrow">
  <tr>
    <td height="20px" style="border:1px solid #999999;padding:4px">
      <table width="100%"  border="0" cellspacing="0" cellpadding="2">
				<tr>
          <td>&nbsp;Page Format</td>
					<td width="5px"></td>
          <td colspan="4">
					<?php
            txt::CheckBox("ckDefault","1","","Change to default value") ;
          ?>
          </td>
        </tr>
				<tr>
					<td colspan="6"><hr></td>
				</tr>
        <tr>
          <td width="50px"><strong>Paper</strong></td>
          <td width="5px">:</td>
          <td colspan="4">
          <select name="cPaper" size="1" onchange="PaperSize(this)">
						<?php
							$vaPaper = PrintDialog::GetPaperSize() ;
							foreach($vaPaper as $value){
								$cSelected = $data["paper"] == $value ? "selected" : "" ;
								echo("<option value=\"$value\" $cSelected>$value</option>") ;
							}
						?>
          </select>
          </td>
        </tr>
        <tr>
          <td colspan="6"><strong>Margin</strong></td>
        </tr>
        <tr>
          <td width="50px">&nbsp;Top</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::$Caption = "mm" ;
            txt::NumberField("nTop",$data['mtop'],3,3,"",5,50) ;
          ?>
          </td>
          <td>&nbsp;Bottom</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::$Caption = "mm" ;
            txt::NumberField("nBottom",$data['mbottom'],3,3,"",5,50) ;
          ?>
          </td>
        </tr>
        <tr>
          <td>&nbsp;Left</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::$Caption = "mm" ;
            txt::NumberField("nLeft",$data['mleft'],3,3,"",5,50) ;
          ?>
          </td>
          <td>&nbsp;Right</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::$Caption = "mm" ;
            txt::NumberField("nRight",$data['mright'],3,3,"",5,50) ;
          ?>
          </td>
        </tr>
				<tr>
					<td colspan="6"><hr></td>
				</tr>
				<tr>
          <td>&nbsp;</td>
					<td width="5px"></td>
          <td colspan="4">
          <?php
						txt::CheckBox("ckTabBaru","N",'',"Open new tab",$data["tab_baru"] == 1) ;
          ?>
          </td>
        </tr>
				<tr>
          <td>&nbsp;</td>
					<td width="5px"></td>
          <td colspan="4">
					<?php
            txt::CheckBox("ckExportCSV","1","","Export CSV") ;
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
            txt::HiddenField("nWidth",$data['pwidth']) ;
            txt::HiddenField("nHeight",$data['pheight']) ;

						txt::ButtonField("cmdPreview","Preview") ;
            txt::$onClick = "CloseForm();" ;
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