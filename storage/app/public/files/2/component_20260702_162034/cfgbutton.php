<?php
  include 'df.php' ;
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
    <td class="panel_main" valign="top">
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr>
          <td width="100px">&nbsp;Level</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::$Button = true ;
            txt::Show("cLevel",GetSetting("CfgButtonSetting",""),"4","4") ;
          ?>
          </td>
        </tr>
        <tr>
          <td width="100px">&nbsp;Keterangan</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::Show("cKeterangan","",40,30,true) ;
          ?>
          </td>
        </tr>
        <tr>
          <td width="100px">&nbsp;</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::$Caption = "Add" ;
            txt::CheckBox("ckAdd","1") ;
          ?>
          </td>
        </tr>
        <tr>
          <td width="100px">&nbsp;</td>
          <td width="5px">&nbsp;</td>
          <td>
          <?php
            txt::$Caption = "Edit" ;
            txt::CheckBox("ckEdit","1") ;
          ?>
          </td>
        </tr>
        <tr>
          <td width="100px">&nbsp;</td>
          <td width="5px">&nbsp;</td>
          <td>
          <?php
            txt::$Caption = "Delete" ;
            txt::CheckBox("ckDelete","1") ;
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
            txt::HiddenField("cFormName",$cFormName) ;
            txt::HiddenField("nPos","0") ;

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
