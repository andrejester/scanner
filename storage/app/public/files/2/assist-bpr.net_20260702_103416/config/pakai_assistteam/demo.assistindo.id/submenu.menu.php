<?php
  include 'df.php' ;
?> 
<submenu:DashboardATM>
["ATM","Null"]
  <submenu:DashboardATM@msSubModul_Tabungan> 

<submenu:DashboardMobile>
["Mobile","Null"] 
  <!--<submenu:MasterData@msSubModul_Mmodal> -->
	<submenu:MobileDigitalBPR@msSubModul_MBanking>
  <submenu:MobileDigitalBPRRG@msSubModul_MBanking>
  ["Konfigurasi","Null"]
    <submenu:KonfigSMS@msSubModul_Messaging>
		["-","#"]
		<submenu:Konfigurasi@msSubModul_H2HCurl>
  ["SMS Masking","Null"] 
    <submenu:TransaksiSMS@msSubModul_Messaging>
		<!--Danagung-->
    <submenu:TransaksiSMSDGO@msSubModul_Messaging>
    ["-","#"]
    <submenu:LaporanSMS@msSubModul_Messaging>
    ["-","#"]
    <submenu:UtilitySMS@msSubModul_Messaging>
	<submenu:TransaksiWhatsApp@msSubModul_Messaging>
  ["-","#"]
	<!--Core-->
	<!--Non Core-->
  <!--<submenu:MobileCollection_Cepiring@msSubModul_Mcollection> -->
  <submenu:MobileCollection@msSubModul_Mcollection>
  <submenu:MobileCollection@msSubModul_Mobile>
  <!--Danagung-->
  <submenu:MobileDigitalBPRDanagungGo@msSubModul_Mmodal>
  <submenu:MasterSkoring_Danagung@msSubModul_Mcollection>
  ["Layanan Virtual Account","Null"]
    <submenu:VirtualAccount@msSubModul_MBanking>
    ["-","#"]
    <submenu:ConfigSNAPPermataVA@msSubModul_MBanking>
  ["Layanan Transfer","Null"]
    <submenu:TransferSNAPPermata@msSubModul_MBanking>