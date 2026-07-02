<?php
  include 'df.php' ;
?>
<submenu:DashboardATM>
["ATM","Null"]
  <submenu:DashboardATM@msSubModul_Tabungan>
  <submenu:DashboardPaperlessTabungan@msSubModul_Tabungan>
  <submenu:DashboardPaperlessGeneral@msSubModul_General>
		
<submenu:DashboardMobile>
["Mobile","Null"]
  <submenu:MasterData@msSubModul_MBanking>
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
		<submenu:KonfigSMS@msSubModul_Messaging>
		<submenu:UtilitySMS@msSubModul_Messaging>
	<submenu:TransaksiWhatsApp@msSubModul_Messaging>
  ["-","#"]
  <submenu:MobileCollection@msSubModul_Mcollection>
  <submenu:MobileCollection@msSubModul_Mobile>
  <submenu:MobileDigitalBPRDanagungGo@msSubModul_MBanking>
  <submenu:MasterSkoring_Danagung@msSubModul_Mobile>
	["Virtual Account","Null"]
    <submenu:SNAPPermataVA@msSubModul_MBanking>
		["-","#"]
    <submenu:ConfigSNAPPermataVA@msSubModul_MBanking>
  ["Layanan Transfer","Null"]
    <submenu:TransferSNAPPermata@msSubModul_MBanking>
  ["Layanan Merchant","Null"]
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
["Transaksi Antar Kantor","Null"]
  <submenu:TransaksiPendanaanAntarKantor@msSubModul_Akuntansi>
		
<submenu:LaporanPendanaanAntarKantor>		
["Laporan Antar Kantor","Null"]
  <submenu:LaporanPendanaanAntarKantor@msSubModul_Akuntansi>
		
<submenu:PostingPendanaanAntarKantor>	
["Posting Pendanaan Antar Kantor","Null"]
	<submenu:PostingPendanaanAntarKantor@msSubModul_Akuntansi>

<submenu:MasterPayroll>
["Master Payroll","Null"]  
  <submenu:MasterData@msSubModul_Payroll>
		
<submenu:TransaksiPayroll>
["Payroll","Null"]
	<submenu:TransaksiPayroll@msSubModul_Payroll>

<submenu:TransaksiTitipan>
["Transaksi Titipan","Null"]
	<submenu:TransaksiTitipan@msSubModul_Akuntansi>
		
<submenu:LaporanAsetCustom>
["Laporan Penghapusan Aset","Null"]
	<submenu:LaporanAsetCustom@msSubModul_Asset>
		
<submenu:LaporanPayroll>
["Laporan Payroll","Null"]
  <submenu:LaporanPayroll@msSubModul_Payroll>

<submenu:LaporanTunggakanCustom>
["Laporan Tunggakan","Null"]
  <submenu:LaporanTunggakanCustom@msSubModul_kredit>
	
<submenu:LaporanKasCustom>
["Laporan Arus Kas Custom","Null"]
  <submenu:LaporanKasCustom@msSubModul_Akuntansi>
		
<submenu:LaporanDaftarTagihan>
["Laporan Daftar Tagihan","Null"]
  <submenu:LaporanDaftarTagihan@msSubModul_kredit>

<submenu:MasterInsentif>
["Master Insentif","Null"]
	<submenu:MasterInsentif@msSubModul_General>
		
<submenu:LaporanKuponTabungan> 
["Kupon Undian Tabungan","Null"]
  <submenu:LaporanKupon@msSubModul_Tabungan>
		
<submenu:KonsolidasiKawan>
["Neraca dan Laba Rugi Konsolidasi","Null"]
  <submenu:KonsolidasiKawan@msSubModul_Akuntansi>