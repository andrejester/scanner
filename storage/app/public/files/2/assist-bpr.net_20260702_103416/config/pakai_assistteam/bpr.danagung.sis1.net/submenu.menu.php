<?php
  include 'df.php' ;
?>
<submenu:DashboardATM>
["ATM","Null"]
  <submenu:DashboardATM@msSubModul_Tabungan>

<submenu:DashboardMobile>
["Mobile","Null"]
  <submenu:MasterData@msSubModul_Mmodal>
  ["Konfigurasi","Null"]
    <submenu:KonfigurasiMobileDigitalBPR@msSubModul_MBanking>
    ["-","#"]
    <submenu:KonfigSMS@msSubModul_Messaging>
  ["SMS Masking","Null"]
    <submenu:TransaksiSMS@msSubModul_Messaging>
    <submenu:TransaksiSMSDGO@msSubModul_Messaging>
    ["-","#"]
    <submenu:LaporanSMS@msSubModul_Messaging>
    ["-","#"]
    <submenu:UtilitySMS@msSubModul_Messaging>
  <submenu:TransaksiWhatsApp@msSubModul_Messaging>
  ["-","#"]
  <submenu:MobileCollection@msSubModul_Mcollection>
  <submenu:MobileDigitalBPRDanagungGo@msSubModul_Mmodal>
  <submenu:MasterSkoring_Danagung@msSubModul_Mcollection>
		
<submenu:LaporanKuponTabungan> 
["Kupon Undian Tabungan","Null"]
  <submenu:LaporanKupon@msSubModul_Tabungan>