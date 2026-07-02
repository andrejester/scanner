<?php
  include 'df.php' ;
?>

<submenu:DashboardMobile>
["Mobile","Null"]
	<submenu:MobileDigitalBPR@msSubModul_MBanking>
  ["Konfigurasi","Null"]
    <submenu:KonfigSMS@msSubModul_Messaging>
		["-","#"]
		<submenu:Konfigurasi@msSubModul_H2HCurl>
	["-","#"]		
  <submenu:MobileCollection@msSubModul_Mcollection>
	["Layanan Virtual Account","Null"]
    <submenu:VirtualAccount@msSubModul_MBanking>
    ["-","#"]
    <submenu:ConfigSNAPPermataVA@msSubModul_MBanking>