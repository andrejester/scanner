<?php
  include 'df.php';
?>
<submenu:DashboardMobile>
["Mobile","Null"]
	<submenu:MobileDigitalBPR@msSubModul_MBanking>
  ["Konfigurasi","Null"]
    <submenu:KonfigurasiMobileDigitalBPR@msSubModul_MBanking>
    ["-","#"]
  <submenu:MobileCollection@msSubModul_Mcollection>
  ["Layanan Virtual Account","Null"]
    <submenu:VirtualAccount@msSubModul_MBanking>
    ["-","#"]
    <submenu:ConfigSNAPPermataVA@msSubModul_MBanking>
  ["Layanan Transfer","Null"]
    <submenu:TransferSNAPPermata@msSubModul_MBanking>