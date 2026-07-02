<?php
  include 'df.php' ;
?>

<submenu:DashboardMobile>
["Mobile","Null"]
	<submenu:MobileDigitalBPRRG@msSubModul_MBanking>
  ["Konfigurasi H2H","Null"]
		<submenu:Konfigurasi@msSubModul_H2HCurl>
	["SMS Masking","Null"]
    <submenu:LaporanSMS@msSubModul_Messaging>
    ["-","#"]
		<submenu:KonfigSMS@msSubModul_Messaging>
    <submenu:UtilitySMS@msSubModul_Messaging>
  ["-","#"]
  <submenu:MobileCollection@msSubModul_Mcollection>
  ["Layanan Virtual Account","Null"]
    <submenu:VirtualAccount@msSubModul_MBanking>
    ["-","#"]
    <submenu:ConfigSNAPPermataVA@msSubModul_MBanking>
  ["Layanan Transfer","Null"]
    <submenu:TransferSNAPPermata@msSubModul_MBanking>

<submenu:MasterInsentif>
["Master Insentif","Null"]
	<submenu:MasterInsentif@msSubModul_General>
			