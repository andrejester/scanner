<?php
  include 'df.php' ;
?>

<submenu:DashboardMobile>
["Mobile","Null"]
	<submenu:MobileDigitalBPR@msSubModul_MBanking>
  ["Konfigurasi","Null"]
    <submenu:KonfigurasiMobileDigitalBPR@msSubModul_MBanking>
    ["-","#"]
    <submenu:KonfigSMS@msSubModul_Messaging>
		["-","#"]
		<submenu:Konfigurasi@msSubModul_H2HCurl>
	["SMS Masking","Null"]
    <submenu:LaporanSMS@msSubModul_Messaging>
    ["-","#"]
		<submenu:KonfigSMS@msSubModul_Messaging>
    <submenu:UtilitySMS@msSubModul_Messaging>
			