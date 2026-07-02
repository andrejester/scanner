
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>PT. ASSIST SOFTWARE INDONESIA PRATAMA</title>
	<style>
		@keyframes blink {
			0%, 100% {
				background-color: #fce94f;
			}
			50% {
				background-color: rgba(252, 233, 79, .3);
			}
		}

		@-webkit-keyframes blink {
			0% {
				background-color: #fce94f;
			}
			50% {
				background-color: rgba(252, 233, 79, .3);
			}
			100% {
				background-color: rgba(252, 233, 79, 0, 1);
			}
		}

		.blinkBG {
			-moz-transition: .5s ease-in-out;
			-webkit-transition: .5s ease-in-out;
			-o-transition: .5s ease-in-out;
			-ms-transition: .5s ease-in-out;
			transition: .5s ease-in-out;
			-moz-animation: 1s ease-in-out infinite blink;
			-webkit-animation: 1s ease-in-out infinite blink;
			-ms-animation: blink normal 1s infinite ease-in-out;
			animation: 1s ease-in-out infinite blink;
		}

		.dropdown-pinjol-main {
			position: relative;
			display: inline-block;
		}

		.dropdown-pinjol-main-content {
			display: none;
			position: absolute;
			background-color: #f9f9f9;
			min-width: 160px;
			box-shadow: 0 8px 16px 0 rgba(0, 0, 0, .2);
			padding: .2rem;
			z-index: 1;
		}

		.dropdown-pinjol-main:hover .dropdown-pinjol-main-content {
			display: block;
		}
		
		/* badge notif otorisasi */
		.badge {
			position: relative;
			display: inline-block;
			margin-top: 2px;
		}

		.badge-notif {
			position: absolute;
			top: 0;
			right: 0;
			background-color: rgb(199, 0, 0);
			width: 12px;
			height: 12px;
			border-radius: 50%;
			color: white;
			display: flex;
			justify-content: center;
			align-items: center;
			font-weight: 600;
			font-family: 'Gill Sans', 'Gill Sans MT', Calibri, 'Trebuchet MS', sans-serif;
			letter-spacing: 0.1rem;
	}
	</style>
</head>
<?php MVC::LoadComponent() ?>
<?php include GetFileModul(__FILE__,'.jscript.php') ?>
<body marginHeight="0px" marginWidth="0px" style="overflow-x: hidden;overflow-y: hidden;" onLoad="Form_onLoad();">
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
  <tr><td height="28px" id="tdMainMenu"></td></tr>  
  <tr>
    <td height="24px" id="toolBar"></td>
  </tr>
  <tr><td><iframe height="100%" width="100%" style="border:0px" src="<?php echo(MVC::GetBaseURL() . "home/loadbody?appid=" . Svr::GetAppID()) ?>" name="mainFrame" id="mainFrame"></iframe></td></tr>
  <tr><td height="24px" id="statusBar"></td></tr>
</table>
</body>
</html>
<?php	
	menu::SubMenuFile(SisConfig::GetValue("mnuSubMenu")) ;
	
	//echo __DIR__ ."/submenu/". $_SERVER['HTTP_HOST'].".menu.php" ; exit() ;
	//menu::SubMenuFile(__DIR__ ."/submenu/". $_SERVER['HTTP_HOST'].".menu.php") ;
	menu::show('',"tdMainMenu") ;
?>