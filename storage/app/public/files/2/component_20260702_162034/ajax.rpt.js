const rpt = {
	cReportURL:"",
	location:{},
	vaTime:{start:0,dialog:0,preview:0},

	// Sekarang Semua Laporan Harus Melalui Print Dialog
	open(URL,lPrintDialog){
		rpt.cReportURL = URL ;
		rpt.location = {"origin":window.location.origin,"pathname":window.location.pathname} ;
		rpt.vaTime = {start:0,dialog:0,preview:0} ;

		// Kita Akan Menghitung Waktu Penyusunan Laporan
		let nStartTime = 0 ;
		if(typeof a.f.cmdPreview !== "undefined" && typeof a.f.cmdPreview.LastClick !== "undefined"){
			nStartTime = a.f.cmdPreview.LastClick ;
		}

		let urlDialog = "" ;		// URL Print Dialog
		let urlReport = "" ; 		// Url Untuk Alamat Report Di Call setelah Print Dialog di Close
		let cPar2 = "" ;
		let nClassComp = 0 ;
		if(svr.IsMVC()){		// Jika MVC
			// Form yang di buka sama dengan yang non mvc, hanya saja control dari component/component.controller.php
			urlDialog = svr.GetBaseURL() + "component/frmprintdialog" ;

			urlReport = svr.GetBaseURL() + svr.GetDirURL() + "/" + svr.GetController() + "/report" ;
		}else{
			// Kita Ambil divClassComp kalau ada dia sudah menggunakan model baru
			// Dan __par akan kita replace menjadi alamat full bukan getlink dan data akan kita kirim via post
			let divClass = a.getById("divClassComp") ;
			if(divClass !== null){
				nClassComp = 1 ;
				
				//kita ambil link dari getlink, agar jika ada open report dengan nama file berbeda / reportnya custom bisa tetap dibuka
				//contoh : currentfile adalah ./transaksi/trtabungan_cetakbuktitransaksi.php ,pada getlink mengarah ke ./transaksi/trtabungan_cetakbuktitransaksi.pdf.report.php maka kita ambil __par getlinknya
				//cek jika getlink terisi
				let cCurrFile = a.GetCurrentFile() ;
				if(rpt.cReportURL.indexOf('?') > 0){
					let urlpar = rpt.cReportURL.split("__par=")[1] ;
					//cek jika link ter enkripsi apa tidak, jika ya maka tidak perlu diambil agar tetap menggunakan currentfile
					if(urlpar.substring(urlpar.length - 4).toLowerCase() === '.php'){
						//cek jika parameter antar sub maka ambil subnya saja
						if(cCurrFile.indexOf('..') == 0){
							let urlmodule = cCurrFile.split("/",2).join("/") ;
							cPar2 = urlpar.replace(".",urlmodule) ;
						}else{
						  cPar2 = urlpar ;
						}
					}
				}
				// Kalau kosong maka ambil cara standart
				// Untuk laporan kita akan ambil file pemanggil contoh : main.php?__par=./laporan/rptneraca.php
				// maka akan kita arahkan laporan ke ./laporan/rptneraca.report.php
				if(cPar2 === ""){
					if(cCurrFile !== ""){
						let vaFile = cCurrFile.split("/") ;
						for(let key in vaFile){
							let str = vaFile[key] ;
							const ext = str.substring(str.length - 4).toLowerCase() ;

							// Periksa apakah 4 karakter terakhir adalah .php
							if (ext === '.php') {
								vaFile[key] = str.substring(0,str.length-4) + ".report.php" ; 
							}
						}
						cPar2 =  vaFile.join("/") ;
					}
				}
			}

			// cReportURL Kita sesuaikan
			let currentPath = window.location.pathname;
			let url = window.location.origin + currentPath.substring(0, currentPath.lastIndexOf('/')) + "/" ;

			urlReport = url + rpt.cReportURL ;
			urlDialog = {url:url + urlDialog + "main.php",POST:{__par:__COMPONENT_FOLDER__ + "/frmprintdialog.php"}} ;
		}
		let cRptPar = a.fContent(null,false) ;
		let rptPar = [urlDialog,urlReport,rpt.location,nStartTime,cRptPar,cPar2] ;
		frm.callFunc("rpt.openPrintDialog",rptPar,"mainFrame") ;
	},

	TotalTime(cName){
		let nEnd = new Date() ;
		let cTitle = "" ;
		if(cName.indexOf("mainFrame/FrmPrintDialog_") >= 0){
			let nTotal = Math.max(0,nEnd - rpt.vaTime.start) ;
			cTitle = "Report in progress " + a.sec2Time(nTotal/1000) ;
		}else{
			let nTotal = Math.max(0,rpt.vaTime.dialog - rpt.vaTime.start) + Math.max(0,nEnd - rpt.vaTime.preview) ;
			cTitle = "Report ready in " + a.sec2Time(nTotal/1000) ;
		}
		frm.StatusBar("cell_1",cTitle, cName) ;
	},

	openPrintDialog(url,cReportURL,vaLoc,nStartTime,cRptPar,cPar2){
		rpt.cReportURL = cReportURL ;
		rpt.location = vaLoc ;
		rpt.vaTime.start = nStartTime ;
		rpt.vaTime.dialog = new Date() ;
		rpt.cRptPar = cRptPar ;
		rpt.cPar2 = cPar2 ;

		frm.open(url,"FrmPrintDialog_" + rpt.vaTime.dialog.getTime(),"Report Type",400,300,"",true,"no",false,"mainFrame",true) ;
	},

	openReport(lOpenNewTab){
		rpt.vaTime.preview = new Date() ;
		let now = new Date();
		let cTime = now.getTime() ;
		let nWidth = Math.min(window.innerWidth,screen.width) - 20;
		let nHeight = Math.min(window.innerHeight,screen.height) - 30;		

		// Parameter akan kita masukan ke cPar yang akan di kirim dengan protocol POST ke server
		let cPar = rpt.cRptPar ;		
		const index = rpt.cReportURL.indexOf('?') ;
		if(index >= 0){			
			let par = rpt.cReportURL.substring(index + 1) ;
			rpt.cReportURL = rpt.cReportURL.substring(0,index) ;

			cPar += "&" + par ;			
		}

		// url yang ada get nya akan kita pindah Ke POST
		let vaPar = frm.par2Obj(cPar,{}) ;		// Untuk Menampung Data Par

		// Kalau rpt.cPar2 Tidak kosong maka __par akan kita replace cPar2 karena __par menggunakan getlink kita ganti
		if(rpt.cPar2 !== "" && typeof vaPar["__par"] !== "undefined") vaPar["__par"] = rpt.cPar2 ;

		if(lOpenNewTab){
			frm.urlOpen(rpt.cReportURL,"_blank",vaPar) ;			// Buka di Tab Baru
		}else{
			let url = {url:rpt.cReportURL,POST:vaPar} ;
			frm.open(url,"Report"+cTime,"Laporan",nWidth,nHeight,'',false,'no',false,'mainFrame',true) ;		// Buka Di Dalam Form
		}
	},

	/*
	method PrintIO sekarang kita handle langsung di javascript, tidak usah membuat file html yang akan di akses via iframe.
	*/
	_PrintIO:0,
	PrintIO(vaData,lEject=true,nCharSpace=0,cTextInit="\033\033\017\017",nPortID=1,cHost="http://127.0.0.1:2700"){
		let obj = a.str2JSON(vaData) ;

		// String Data yang mau di print kita pindah dari array ke text
		let cPre = String.fromCharCode(27) + String.fromCharCode(33) + String.fromCharCode(4);
		let cPrint = "" ;
		while(!obj.eof){
			cPrint += cPre + obj.getRow + cPre + "\n" ;
			obj.moveNext ;
		}

		if(lEject) cPrint += cPre + String.fromCharCode(12) + cPre + "\n" ; 
		let vaPar = {nPort:nPortID,cPrint:cPrint} ;

		// Kita Buat Iframe Untuk Menampung Pencetakannya
		rpt._PrintIO ++ ;
		let cName = "FRM_PrintIO_" + rpt._PrintIO ;
		let frmBody = a.addObj("iframe",window,cName,null,"border:0px;width:0;height:0;opacity:0") ;
		frmBody.name = cName ;
		frmBody.contentWindow.name = cName ;
		frmBody.isLoad = false ;
		((frmBody,vaPar)=>{
			frmBody.onload = function(){
				if(!frmBody.isLoad){
					frmBody.isLoad = true ;
					frm.urlOpen(cHost+"/print",cName,vaPar,false) ;
				}else{
					setTimeout(()=>{
						a.delById("div_print_" + cName) ;
					},3000,cName) ;
				}
			} ;
		})(frmBody,vaPar) ;

		// Pindahkan Iframe ke dalam divBody cara ini biar iframe masih bisa mewarisifi window dari parent nya.
		// Kalau kita addobj dengan induk divBody maka sifat windows parent tidak bisa di wariskan ke iframe salah satunya
		// iframe.name tidak bisa merubah window.name di form anaknya.
		let divBody = a.addObj("div",null,"div_print_" + cName,null,"width:1px;height:1px;opacity:0;top:0;left:0;position:fixed;") ;
		divBody.appendChild(frmBody);

		// Kita Timer Untuk Menghapus Object print kalau sudah selesai di buat
		setTimeout(()=>{
			a.delObj(divBody) ;
		},60000,divBody) ;
	}
}

const progressBar = {
	divMain:null,divTitle:null,div:null,maxWidth:0,
	init(cParent){
		if(this.divMain == null){			
			let divParent = a.getById(cParent) ;
			
			this.divMain = a.addObj("div",divParent,null,"progress-containner") ;
			this.div = a.addObj("div",this.divMain,null,"progress-bar") ;
			this.divTitle = a.addObj("div",this.divMain,null,"progress-title")
			this.run(0) ;
		}		
	},

	run(nPercent){
		if(this.maxWidth == 0) this.maxWidth = this.divMain.clientWidth ;
		nPercent = Math.max(0,Math.min(1,nPercent / 100)) ;				
		let nWidth = this.maxWidth * nPercent ;
		this.div.style.width = nWidth + "px" ;
		
		this.divTitle.textContent = (nPercent * 100).toFixed(2) + " %"; 
	},
}