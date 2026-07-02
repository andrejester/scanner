<?php
  include 'df.php' ;
?>
<submenu:DashboardATM>
["ATM","Null"]
  <submenu:DashboardATM@msSubModul_Tabungan>

<submenu:DashboardMobile>
["Mobile","Null"]
  <submenu:MasterData@msSubModul_MBanking>
  ["Konfigurasi","Null"]
    <submenu:KonfigurasiMobileDigitalBPR@msSubModul_MBanking>
    ["-","#"]
    <submenu:KonfigSMS@msSubModul_Messaging>
  <submenu:MobileCollection@msSubModul_Mcollection>
  ["SMS Masking","Null"]
    <submenu:TransaksiSMS@msSubModul_Messaging>
    <submenu:TransaksiSMSDGO@msSubModul_Messaging>
    ["-","#"]
    <submenu:LaporanSMS@msSubModul_Messaging>
	<submenu:TransaksiWhatsApp@msSubModul_Messaging>
  ["-","#"]
  <submenu:MobileDigitalBPRDanagungGo@msSubModul_MBanking>
  <submenu:MasterSkoring_Danagung@msSubModul_Mobile>
  ["Layanan Virtual Account 123","Null"]
    <submenu:SNAPPermataVA@msSubModul_MBanking>
    ["-","#"]
    <submenu:ConfigSNAPPermataVA@msSubModul_MBanking>
  ["Layanan Transfer","Null"]
    <submenu:TransferSNAPPermata@msSubModul_MBanking>
	["Layanan Merchant 000","Null"]
    <submenu:MobileMerchantMandiri@msSubModul_Merchant>

<submenu:MasterUndianBerhadiah>	
["Master Undian Berhadiah","Null"]
	<submenu:MasterUndianBerhadiah@msSubModul_Point>
		
<submenu:LaporanUndianBerhadiah>
["Laporan Point dan Hadiah","Null"]
	<submenu:LaporanPointUndian@msSubModul_Point>
		
<submenu:PostingAkhirBulanPoint>
["Posting Saldo dan Undian","Null","/share/images/menu/Gear.gif"]
	<submenu:PostingAkhirBulanPointUndian@msSubModul_Point>
	
<submenu:MasterPendanaanAntarKantor>	
["Master Pendanaan Antar Kantor","Null"]
	<submenu:MasterPendanaanAntarKantor@msSubModul_Akuntansi>
			
<submenu:TransaksiPendanaanAntarKantor>		
["Transaksi Pendanaan Antar Kantor","Null"]
  <submenu:TransaksiPendanaanAntarKantor@msSubModul_Akuntansi>
		
<submenu:LaporanPendanaanAntarKantor>		
["Laporan Pendanaan Antar Kantor","Null"]
  <submenu:LaporanPendanaanAntarKantor@msSubModul_Akuntansi>
		
<submenu:PostingPendanaanAntarKantor>	
<submenu:PostingPendanaanAntarKantor@msSubModul_Akuntansi>
	
<submenu:DashboardKonversi>
["Konversi Data","Null"]  
	<submenu:DashboardKonversi@msSubModul_Konversi>
	
<submenu:LaporanAsetCustom>
["Laporan Penghapusan Aset","Null"]
	<submenu:LaporanAsetCustom@msSubModul_Asset>

<submenu:LaporanTunggakanCustom>
["Laporan Tunggakan","Null"]
	<submenu:LaporanTunggakanCustom@msSubModul_kredit>