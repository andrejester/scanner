const frm = {
	/*
	URL							= URL ( URL bisa berisi text url contoh : http://aa.sis1.net/)
										Atau URL bisa berisi Object yang isinnya :			
										
										{url:"http://....",POST:{key:value,key2:value2}}
										
										kalau berisi Object dia akan mengirim POST melalui protocol POST bukan header.

	cFormName				= Form Name untuk yang di buka via menu dia berisi mnuID
	cTitle					= Judul Form
	nWidth					= Lebar
	nHeight					= Tinggi
	cBackColor			= Warna Background
	lShowModal			= Show Modal
	cFormScroll			= Type Scroll Form
	lHideToolBox		= Hidden Toolbok min,close
	cFrameName			= Target FrameName (mainFrame)
	lReport					= Jenis Laporan
	cNumForm				= Form Number ( Khusus Open dari Menu untuk menyimpan ke dalam syslog )
	vaButtonAllowed = untuk Button yang di bolehkan berisi object {"cmdAdd":true,"cmdEdit":false,"cmdDelete":true}
										Jika dia berisi false maka tombol kita hidden
	*/
  open(oURL,cFormName,cTitle,nWidth,nHeight,cBackColor,lShowModal,cFormScroll,lHideToolBox,cFrameName,lReport,cNumForm,vaButtonAllowed){
		let ow = window ;
		let _url = oURL ;
		let vaPOST = {} ;
		if(typeof oURL == "object"){
			_url = oURL.url ;
			vaPOST = oURL.POST ;
		}
		
		// Jika Kita Buka cFrameName tidak Kosong Maka kita akan frm.callChildFunc
		if(cFrameName && cFrameName !== "" && cFrameName !== ow.name){
			let vaPar = Object.values(arguments) ;
      frm.callChildFunc("frm.open",vaPar,cFrameName) ;
			return true ;
    }
		
    if(!cFormScroll) cFormScroll = "no" ;
		// Check Jika jenisnya MVC dan _url tidak di awali http:// atau https:// maka kita masukkan BASE _url
		if(__COMPONENT_TYPE__ == "mvc"){
			if(_url.substring(0,7) !== "http:\/\/" && _url.substring(0,8) !== "https:\/\/"){
				if(_url.substring(0,1) == "/") _url = _url.substring(1) ;
				_url = __BASE_URL__ + _url ;
			}
			// Tambah Parameter mnuID
			vaPOST.mnuid = cFormName ;
		}

		// Kita Akan Bandingkan Kalau Host Sama dengan Form yang di buka kita tidak usah memberikan token
		// Kalau Berbeda dianggap dia Submodule kita kirimkan token via post
		let lSameServer = true ;
		if(_url.substring(0,7) == "http:\/\/" || _url.substring(0,8) == "https:\/\/"){
			const winURL = new URL(_url);
			if(window.location.origin !== winURL.origin) lSameServer = false ;
		}

    // Kalau Show Modal Kita Buat Background Sebesar Monitor supaya bagian Belakang Tidak Bisa di Buka
		let divModal = null ;
    if(lShowModal) divModal = a.addBack(null) ;

		// Formname kita ikutnya Induk nya biar bisa di buka dengan formname sama di berapa form induk
		cFormName = ow.name + "/" + cFormName ;	
		gData.Save(["divModal",cFormName],divModal) ;

    if(lReport == null) lReport = false ;
    let lmdi = ow.name == "mainFrame" ;

    // Kita akan cek apakah Form sudah di buka atau tidak, kalau sudah tinggal kita aktifkan
		// Kalau sudah di buka divMain ada isinya, kalau tidak ada maka divMain null
		let divMain = frm.frmReOpen(cFormName) ; 
    if(divMain !== null){
      // Kalau Sudah Ada Berarti dia sudah di buat dengan status Minimize Tinggal kita munculkan saja dan abaikan menu di bawahnya
      return true ;
    }else{
			/*
			Susunan Form
				Parent 												= div (cName)
				parent -> Header							= div Header Form (cName-header)
				parent -> Toolbar	-> min			= div Toolbar ( cName-min )
				parent -> Toolbar -> help			= div Toolbar ( cName-help )
				parent -> Toolbar -> close 		= div Toolbar ( cName-close)
				parent -> Body								= div Border Form ( cName-border )
				parent -> Body -> Form Body		= iframe Body Chield ( cName_formbody )
				parent -> StatusBar						= div Status Bar (cName_StatusBar)
			*/
			//open(oURL,cFormName,cTitle,nWidth,nHeight,cBackColor,lShowModal,cFormScroll,lHideToolBox,cFrameName,lReport,cNumForm,vaButtonAllowed){
			if(!cFormName.toLowerCase().includes("frmprintdialog")) {
      	//console.log("Form Name = ",cFormName) ;
				//console.log("Title Name = ",cTitle) ;
				//console.log("Frame Name = ",cFrameName) ;
				//console.log("Num Form = ",cNumForm) ;
				
				frm.SaveLog("03","Open Form "+cTitle) ;
			}
			divMain = a.addObj("div",ow,cFormName,"win_main win_main_custom fadein") ;

      // Create Header - Parent (divMain)
      let divHeader = a.addObj("div",divMain,cFormName + '-header',"win_header win_header_focus") ;
			((divHeader,cFormName,ow)=>{
				divHeader.onmousedown = function(e){frm.startMove(e,cFormName,ow) ;} ;
			})(divHeader,cFormName,ow) ;
			
      // Create Title - Parent (Header)
			let divTitle,divPanduan,divMin,divClose = null ;

			// Div Title
      divTitle = a.addObj("div",divHeader,cFormName + '-title',"win_title",null,cTitle) ;

      // Create Toolbar  - Parent (Header)
      if(!lHideToolBox){
        if(!lReport && !lShowModal && lmdi){
          divMin = a.addObj("div",divHeader,cFormName + '-min',"win_icon_min",null,"&mdash;") ;
          divMin.title = "Minimize" ;
          divMin.onclick = function(){
						if(!txt.canClick(divMin)) return false ;
						frm.min(cFormName,lReport) ;
					} ;

          divPanduan = a.addObj("div",divHeader,cFormName + '-panduan',"win_icon_panduan",null,"?") ;
					divPanduan.title = "Help" ;
					divPanduan.onclick = function(){
						if(!txt.canClick(divPanduan)) return false ;
						frm.frmHelp(cFormName) ;
					} ;
        }else{
          lmdi = false ;
        }

        divClose = a.addObj("div",divHeader,cFormName + '-close',"win_icon_close",null,"✕") ;
        divClose.title = "Close" ;
        divClose.onclick = function(){
					if(!txt.canClick(divClose)) return false ;
					frm.close(cFormName,lReport,cTitle,cNumForm) ;
				} ;
      }

			// Kita Buat Object Ifram terlebih dahulu
			// Object iframe kita buat opcity:0 dulu biar tidak terlihat waktu rendering css 
			// setelah document di loads akan kita kembalikan opacity=1
			// Jika dalam 3 detik belum muncul juga maka juga akan kita munculkan
			lSameServer = false ;
			let frmBody = a.addObj("iframe",window,cFormName + "_formbody",null,"border:0px;width:100%;height:100%;opacity:0;") ;
			frmBody.scrolling = cFormScroll ;
			frmBody.name = cFormName ;
			frmBody.contentWindow.name = cFormName ;
			frmBody.isLoad = lSameServer ;
			frmBody.fromOpenForm = true ;						// properti ini juga kita gunakan Untuk mendeteksi kalau iframe di buka dengan openForm
			if(lSameServer) frmBody.src = _url ;
			((frmBody,cFormName,cTitle,lReport,vaPOST)=>{
				frmBody.onload = function(){
					// Kalau dia tidak lSameServer dan Pertamakali onload karena iframe masih kosong dan akan kita isi dengan url yang benar
					if(!frmBody.isLoad){
						frmBody.isLoad = true ;
						frm.urlOpen(_url,cFormName,vaPOST) ;
					}else{
						a.delById("formData_" + cFormName) ;
						frmBody.style.opacity = 1 ;
						frm.onLoad(cFormName,cTitle,lReport) ;
					}
				} ;
			})(frmBody,cFormName,cTitle,lReport,vaPOST) ;

			// Dalam 3 Detik kalau belum muncul akan kita munculkan
			setTimeout((frmBody)=>{
				frmBody.style.opacity = 1 ;
			},3000,frmBody) ;
			
			// Pindahkan Iframe ke dalam divBody cara ini biar iframe masih bisa mewarisifi window dari parent nya.
			// Kalau kita addobj dengan induk divBody maka sifat windows parent tidak bisa di wariskan ke iframe salah satunya
			// iframe.name tidak bisa merubah window.name di form anaknya.
			// Add Body - Parent (divMain)
      let divBody = a.addObj("div",divMain,cFormName + "-border","win_border") ;
			divBody.appendChild(frmBody);

			// Buat Status Bar di setiap Form untuk Informasi
			let divSBar = a.addObj("div",divBody,cFormName + "_StatusBar","win_sbar") ;			
			sBar.add("cell_1","",null,"left") ;
			sBar.add("cell_2","","100px","center") ;
			divSBar.data = sBar.show("FrmStatusBar",divSBar,"1px") ;
			divSBar.data["cell_2"].style.display = "none" ;
			let nStartTime = new Date();
			gData.obj(["frm","buttonAllowed"],vaButtonAllowed) ;

			// Paramter Kita Simpan Ke Variable
			gData.Save(["frm",cFormName],{buttonAllowed:vaButtonAllowed,name:cFormName,title:cTitle,divMain:divMain,divHeader:divHeader,divBody:divBody,
																		frmBody:frmBody,divTitle:divTitle,divMin:divMin,divPanduan:divPanduan,divClose:divClose,divSBar:divSBar,
																		StartTime:nStartTime}) ;
    }

    // Tambahkan kedalam Windows List dengan Syarat jenis nya MDI
    if(lmdi){
			frm.addList(cFormName,cTitle,ow,divMain,lReport) ;
			frm.changeForm(cFormName) ;
		}     

		// Kita Tambah Status Bar Height + 25
		const oBorder = gData.Get(["frm",cFormName,"divBody"]) ;
		const oSBar = gData.Get(["frm",cFormName,"divSBar"]) ;
		const oFrame = gData.Get(["frm",cFormName,"frmBody"]) ;

		nHeight = parseInt(nHeight) + oSBar.offsetHeight ;

    const nWinHeight = Math.min(ow.document.body.clientHeight,screen.height) ;
    const nWinWidth = Math.min(ow.document.body.clientWidth,screen.width) ;

		const nTop = Math.max((nWinHeight - nHeight)/2,0) + document.body.scrollTop ;
    const nLeft = Math.max((nWinWidth - nWidth)/2,0) + document.body.scrollLeft ;

    with(divMain.style){
      width = nWidth + "px" ;
      height = nHeight + "px" ;
      top = nTop + "px" ;
      left = nLeft + "px" ;
    }

		const nHeadHeight = 29 ;
		oBorder.style.height = (divMain.offsetHeight - nHeadHeight) + "px" ;
    oBorder.style.top = nHeadHeight  + "px" ;
    oBorder.style.width = divMain.offsetWidth + "px" ;
    oBorder.style.width = (divMain.offsetWidth - (oBorder.offsetWidth - divMain.offsetWidth))  + "px" ; 
		oFrame.style.height = oBorder.clientHeight - oSBar.offsetHeight ;

		a.setObjIndex(divMain) ;
		//if(ow.document.getElementById(cFormName + '-header') != null){
			//ow.document.getElementById(cFormName + '-header').click() ;
		//}
		//console.log(divMain) ;
  },
	par2Obj(par,vaPar={}){
		let va = par.split("&") ;
		for(let par of va){
			// Temukan indeks dari '=' pertama
			// Gunakan indeks untuk membagi string menjadi dua bagian
			const index = par.indexOf('=');
			let key = par.substring(0, index) ;
			let value = par.substring(index + 1) ;
			if(key !== "") vaPar[key] = value ;
		}
		return vaPar ;
	},
	/*
	urlOpen kita gunakan untuk membuka url Iframe dengan mengirimkan Token Via Post
	*/
	urlOpen(url,target,vaPar={},lSendToken=true){
		// url yang ada get nya akan kita pindah Ke POST
		const index = url.indexOf('?') ;
		if(index >= 0){			
			let par = url.substring(index + 1) ;
			url = url.substring(0,index) ;
			vaPar = frm.par2Obj(par,vaPar) ;
		}

		let form = a.addObjById("formData_" + target,"form",null,null,"width: 1px;height: 1px;position: fixed;top: 0px;left: 0px;opacity: 0") ;
		form.innerHTML = "" ;
		form.target = target ;
		form.method = "POST" ;
		form.action = url ;

		if(lSendToken){
			if(typeof vaPar.__token == "undefined") vaPar.__token = svr.GetToken() ;
			if(typeof vaPar.appid == "undefined") vaPar.appid = svr.GetAppID() ;
		}		

		// Buat Object input hidden untuk menampung parameter
		// kita akan kirim ke server dengan POST biar tidak terbaca di url
		for(let key in vaPar){
			let input = a.addObj("input",form) ;
			input.name = key ;
			input.type = "hidden" ;
			input.value = vaPar[key] ;
		}
		form.submit() ;

		// Kalau dalam 3 Detik object form belum di hapus maka kita hapus
		setTimeout(()=>{
			a.delObj(form) ;
		},3000,form) ;
	},
	StatusBar(cName,value,cWinName=null){
		if(cWinName == null) cWinName = window.name ;
		let divBar = gData.Get(["frm",cWinName,"divSBar"]) ;
		if(divBar !== null && typeof divBar.data !== "undefined" && divBar.data !== null && typeof divBar.data[cName] !== "undefined"){
			divBar.data[cName].style.display = value !== "" ? "" : "none" ;
			divBar.data[cName].innerHTML = value ;
		}
	},
	frmHelp(cFormName){
		frm.callChildFunc("Form_onHelp",null,cFormName) ;
	},
	frmReOpen(cFormName){
		let vaFrm = gData.Get(["frm",cFormName]) ;

		// Kita akan lihat apakan Form ada, dengan cara kita cek frmBody.contentWindow kalau ada berarti form masih ada
		// kalau tidak ada maka form sudah di close
		if(vaFrm == null || vaFrm.frmBody == null || vaFrm.frmBody.contentWindow == null){
			// Kita hapus di gData dulu 
			gData.Save(["frm",cFormName],null) ;
			return null ;
		} 

		let oDiv = vaFrm.divMain ;
    oDiv.className = "win_main win_main_custom fadein" ;
    oDiv.style.display = "block" ;
    setObjIndex(oDiv) ;
    frm.changeForm(cFormName) ;
		
		gData.Save(["winList",cFormName,"min"],false) ;
		return oDiv ;
  },
  clickForm(cName){
		let va = gData.Get(["frm",cName]) ;
    if(va.divHeader !== null) va.divHeader.className = "win_header win_header_focus" ;
    
    // Jika Old Windows Tidak Kosong Dan Tidak sama dengan Name maka kita lost focus kan.
    frm.changeForm(cName) ;

    if(va.divMain !== null) setObjIndex(va.divMain) ;
  },
  changeForm(cName){
		let n = cName.lastIndexOf('/');

		// Name itu sekarang menggunakan induk pemanggilnya kita ambil
		// Untuk change Form kita harus lihat di di induk yang sama contoh /master/formmaster berarti intuk /master
		let cParent = n === -1 ? "/" : cName.substring(0,n) ;

    let cOldWin = gData.Save([cParent,"cOldWinName"],cName) ;
		// Kalau ada Background Penutup kita hapus
		a.delObj(gData.Save(["frm",cName,"divBack"],null)) ;

    // Jika ada di Windows List berarti mdi kalau tidak ada abaikan
    if(gData.Get(["winList",cName]) !== null){
      if(cOldWin !== null && cOldWin !== "" && cOldWin !== cName){
				let vaOld = gData.Get(["frm",cOldWin],null) ;
				if(vaOld !== null){
					if(vaOld["divHeader"] !== null) vaOld["divHeader"].className = "win_header win_header_blur" ;
					// Kita akan buat object di atas windows untuk menutup object nya
					// biar kalau di click bisa pindah ke windows nya
					if(vaOld.divMain !== null){
						oldBack = a.addBack(cOldWin + "-blur-",vaOld.divMain) ;
						oldBack.style.width = vaOld.divMain.offsetWidth ;
						oldBack.style.height = vaOld.divMain.offsetHeight - 30 ;
						oldBack.style.top = "30px" ;
						gData.Save(["frm",cOldWin,"divBack"],oldBack) ;
						((div,cOldWin)=>{
							div.onclick = ()=>{
								frm.clickForm(cOldWin) ;
							} ;
						})(oldBack,cOldWin) ;
					}
				}

        gData.Save(["winList",cOldWin,'active'],false) ;
      }
			
			let divHeader = gData.Get(["frm",cName,"divHeader"]) ;
      if(divHeader !== null) divHeader.className = "win_header win_header_focus" ;

			gData.Save(["winList",cName,"active"],true) ;
    
      // Buat List untuk mengurutkan Windows yang aktif, sehingga kalau di tutup akan focus ke form terakhir
			let cList = gData.Get("winList_Index",",") ;
      cList = cList.replace("," + cName + ",",",") ;
      if(cList.indexOf("," + cName + ",") == -1) cList += cName + "," ;
			gData.Save("winList_Index",cList) ;
    }
  },
	_onLoad(va){
		// Token Akan kita Ambil dari Induk bukan lagi dikirim Setiap Form
		frm.callFunc("svr.GetToken",[],"",(par)=>{
			svr._saveToken(par) ;

			document.onscroll = ()=>{
				with(document.body){
					scrollLeft = 0 ;
					scrollTop = 0 ;
				}
			}
			if(typeof Form_onLoad == 'function') Form_onLoad() ;		
		},1000) ;

		// Jika dia ada Panduan maka kita akan response true untuk memunculkan icon help di window
		let retval = {panduan:false} ;
		if(typeof Form_onHelp == 'function') retval.panduan = true ;
		return retval ;
	},
  onLoad(cName,cTitle,lReport){
		// Hitung Waktu Pembuatan Laporan Disini
		if(lReport) frm.callFunc("rpt.TotalTime",cName,"mainFrame") ;

		// Untuk Model baru komunikasi ke Child / parent Menggukanan callFunc
		vaForm = gData.Get(["frm",cName]) ;
		frm.callChildFunc("frm._onLoad",null,cName,(data)=>{
			// Jika ada Form_onHelp maka Icon Panduan kita munculkan
			if(data.panduan){
				if(vaForm.divPanduan !== null){
					vaForm.divPanduan.style.display = "block" ;
				} 
			}

			// Update Response Time
			// Kalau Form Bukan frmprint dialog karena kalau print dialog akan di isi waktu penyusunan laporan
			if(cName.toLowerCase().indexOf("frmprintdialog") < 0){
				let nStart = vaForm.StartTime ;
				let nEndTime = new Date();
				let nResponseTime = (nEndTime - nStart) / 1000 ;
				frm.StatusBar("cell_1","Load form in " + nResponseTime.toFixed(3) + " sec ",cName) ;
			}
		}) ;
  },
  min(cName,lReport){
    let divMain = gData.Get(["frm",cName,"divMain"],null) ;
    if(divMain !== null){
      divMain.className = "win_main win_main_custom fadeout" ;

      // Pindahkan Posisi Form yang Hiden ke paling Depan artinya urutan terakhir
      // Supaya Form berikutnya bisa di Status Aktif
			let cList = gData.Get("winList_Index",",") ;
      cList = cList.replace("," + cName + ",",",") ;
      cList = "," + cName + cList ;
			gData.Save("winList_Index",cList) ;
	
      // Buka Form yang terakhir
      frm.openLast(cName) ;

			divMain.style.display = "none" ;
			gData.Save(["winList",cName,"active"],false) ;
			gData.Save(["winList",cName,"min"],true) ;
    }
  },
	_close(lReport){
		let lClose = true ;
		if(!lReport){
			if(typeof Form_onClose == 'function') lClose = Form_onClose() ;
		}
		return lClose ;
	},
  close(cName,lReport,cTitle,cNumTitle,lSubModule=false){
    // Kalau Name Kosong Maka Kita ambil Dari Induk nya, karena dia di execusi dari Dalam Iframe.
		// Jika winname == "subModule" Kita juga akan call induk, dan winname akan kita kosongkan.
    if(typeof cName == "undefined" || cName == null || cName == "" || window.name == "subModule"){
			let winName = window.name == "subModule" ? "" : window.name ;
			frm.callFunc("frm.close",[winName,lReport,cTitle,cNumTitle,true]) ;
			return true ;
    }

		// Kita Akan Cari apakah ada Form_onClose kalau ada kita call, kalau tidak ada langsung kita close windows nya
		if(lReport){
			frm.closeForm(cName) ;
		}else{
			frm.callChildFunc("frm._close",[lReport],cName,(lClose,status)=>{
				if(lClose || status !== 200){
					frm.closeForm(cName) ;
				}
			},1000) ;
		}
		if(typeof cTitle != "undefined"){ 
			frm.SaveLog("12","Close Form " + cTitle) ;
		}
	},
	SaveLog(cMTI,cMessage){
		a.ajax(svr.GetComponentPath() +"/savelog","SaveLog()","cMTI="+cMTI+"&cMessage="+cMessage) ;
	},
	closeForm(cName){
		let vaForm = gData.Get(["frm",cName]) ;
		if(vaForm == null) return true ;

		let od = vaForm.divMain ;
		let ob = gData.Save(["divModal",cName],null) ;
		od.className = "win_main win_main_custom fadeout" ;

		// Hapus Element di json
		frm.delList(cName) ;

		// Hapus Terlebih dahulu Form yang terakhir
		let cList = gData.Get("winList_Index",",") ;
		cList = cList.replace("," + cName + ",",",") ;
		gData.Save("winList_Index",cList) ;

		gData.Save(["frm",cName],null) ;
		// Buka Form yang terakhir

		a.delObj(od);
		a.delObj(ob);

		frm.openLast(cName) ;
	},
  // Kita Akan mencari Form terakhir yang di buka kalau ada maka Form itu akan kita Buka sebagai Form Active
  openLast(cName){    
    let va = gData.Get("winList_Index",",").split(",") ;
    let cLast = "" ;
    if(va.length >= 3) cLast = va [va.length-2] ;
    if(cLast !== "") frm.changeForm(cLast) ;
  },
  startMove(e,cName,ow){
		const vaForm = gData.Get(["frm",cName],null) ;
		if(vaForm == null) return true ;
    let oMove = vaForm.divMain ; 
    let oTitle = vaForm.divTitle ;

		frm.clickForm(cName) ;
    setObjIndex(oMove) ;
    a.obj_move_start(oMove,e,null,null,ow) ;
  },
  addList(cName,cTitle,ow,oDiv,lReport){
		gData.Save(["winList",cName],{title:cTitle,active:true,frm:oDiv,ow:ow,min:false,report:lReport}) ;
    a.slideBarItem() ;
  },
  delList(cName){
		gData.Save(["winList",cName],null) ;
    a.delById("__sl_item_" + cName + "__",a.gWin) ;
  },
  initButton(cFormName){
    a.ajax("../component/cfgbutton.ajax.php","initButton()","cFormName="+cFormName,function(cData,nStatus){
      if(typeof a.f.cmdAdd !== "undefined" && cData.substring(0,1) == "0") a.delObj(a.f.cmdAdd);
      if(typeof a.f.cmdEdit !== "undefined" && cData.substring(1,2) == "0") a.delObj(a.f.cmdEdit);
      if(typeof a.f.cmdDelete !== "undefined" && cData.substring(2,3) == "0") a.delObj(a.f.cmdDelete);
      if(typeof a.f.cmdSetting !== "undefined" && cData.substring(3,4) == "0") a.delObj(a.f.cmdSetting);  
    }) ;
  },
  cfgButton(cFormName){
    frm.open("main.php?__par=../component/cfgbutton.php&cFormName="+cFormName,"FrmCfgButton","Setting Button",400,300,'',true) ;
  },
	isValidSaving(_frm,callBack){
		let lRetval = true ;
		let cInput = ",text,number,radio,password,date," ;
		let f = null ;
		let cClass = "" ;

		if(!_frm) _frm = a.f ;	
		for(n=0;n<_frm.length;n++){
			f = _frm.elements[n] ;
			if(cInput.indexOf(","+f.type.toLowerCase()+",") >= 0 ){
				lRetval = txt.fieldValidated(f) && lRetval ;
			}
		}

		if(callBack){
			callBack(lRetval) ;
		}else if(!lRetval){
			frm.snackBar("Isi data tidak lengkap, Data tidak bisa disimpan ......!","error") ;
		}
		return lRetval ;	
	},
	hiddenButton(cmd,lPar){
		cVisible = lPar ? "hidden" : "visible" ;
		
		cmd.parentNode.style.visibility = cVisible ; 
	},
  setupComponent(_frm,lPar){
		let cInput = ",text,number,radio,checkbox,password,date,file,select-one," ;
		let f = null ;
		let vaForm,span,cVisible = {} ;
		vaForm = gData.Get(["frm",window.name],{}) ; 
		for(n=0;n<_frm.length;n++){
			f = _frm.elements[n] ;
			if(cInput.indexOf(","+f.type.toLowerCase()+",") >= 0 ){
				f.disabled = !lPar ;      
			}

			// Mengatur Component Required
			txt.initRequired(f) ;

			if(f.name == "cmdSave" || f.name == "cmdCancel"){
				f.disabled = !lPar ;
				frm.hiddenButton(f,!lPar) ;
			}

			if(f.name == "cmdAdd" || f.name == "cmdEdit" || f.name == "cmdDelete" || f.name == "cmdSetting"){
				f.disabled = lPar ;
				frm.hiddenButton(f,lPar) ;
			}
		}

		// Update StatusBar Untuk Jenis Form
		setTimeout(()=>{
			if(typeof document.form1 !== "undefined" && typeof document.form1.nPos !== "undefined"){
				let va = {1:"Add",2:"Edit",3:"Delete"} ;
				let status = typeof va [a.f.nPos.value] !== "undefined" ? va[a.f.nPos.value] : "Display" ;
				frm.StatusBar("cell_2","Status : " + status) ;
			}
		},500) ;
  },
  disableComponent(form,lPar){
		const vaInput = ["text","radio","password","file","select-one"] ;
		let vaButton = [] ;
		for(let field of form){
			// Cari Input
			if(vaInput.includes(field.type)){
				field.disabled = lPar ;
			} 
			
			// Cari Button kita akan hapus
			if(field.type == "button" && typeof field.Required !== "undefined"){
				field.disabled = lPar ;
				vaButton.push(field) ;
			} 			
		}
		
		if(lPar){
			for(let button of vaButton){
				a.delObj(button.parentNode) ;
			}
		}		
  },
	snackBar(title,type="",position="top"){
		// Kalau window.name bukan mainFrame maka kita call yang di main Frame, dan pakai callFunc untuk yang submodule biar tidak error
		if(window.name == "root/FrmLogin" || window.name == "root"){
			//return frm.callFunc("frm.snackBar",Object.values(arguments),"root/FrmLogin_formbody") ;
		}else{
		  if(window.name !== "mainFrame") return frm.callFunc("frm.snackBar",Object.values(arguments),"mainFrame") ;
		} 
		let parentWidth = window.document.body.clientWidth ;
		let color = type.toLowerCase() == "error" ? "_error" : (type.toLowerCase() == "success" ? "_success" : "") ;
		const now = new Date();
		const seconds = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();

		position = typeof position == "undefined" || position !== "bottom" ? "_top" : "" ;

		// Kita Akan hapus kalau ada sisa snackBar yang lebih dari 3 detik dan belum di hapus
		const divs = window.document.querySelectorAll("div#divSnackBar");
		for (const oldDiv of divs) {
			if(typeof oldDiv.timeStart == "number"){
				let nTTL = seconds - oldDiv.timeStart ;
				if(nTTL < 0 || nTTL > 3) a.delObj(oldDiv) ;
			} 
		}

		// Buat Background Snackbar nya
		let divBack = a.addObjById("snackBack" + position,"div",null,"snackbar_back" + position) ;
		let nIndex = a.setObjIndex() + 10 ;
		divBack.style.zIndex = nIndex ;		

		let div = a.addObj("div",divBack,"divSnackBar","snackbar" + color,null,title) ;
		div.style.maxWidth = div.offsetWidth ;
		div.timeStart = seconds ;

		// Buat Event Jika Style Animasinya selesai maka kita del object nya
		div.addEventListener('animationend', ()=>{
			a.delObj(div) ;
		});
	},
	/*
	function untuk menyimpan Transaksi pada Form kita, dia sudah melakukan pengecekan untuk Client site Validation
	*/
	save(url,method,param,callBack){
		if(frm.isValidSaving()){
			method = method == "undefined" || method == "" ? "ValidSaving" : method ;
			param = method == "undefined" || param == "" ? a.fContent(null,true) : param ;
			a.confirm("Data Akan Disimpan ?","Konfirmasi",function(par){
				if(par){
					a.wait(30,"Data Sedang Disimpan .....") ;
					a.ajax(url,method,param,function(obj){
						let nResponse = (a.Response.end - a.Response.start) / 1000 ;
						frm.StatusBar("cell_1","Data Saved in " + nResponse.toFixed(3) + " sec ") ;
						a.endwait() ;
						if(obj.dataType == "string" && obj.data.toLowerCase() == "ok") frm.snackBar("Data Telah Disimpan ....","success") ;
						callBack(obj) ;
					}) ;
				}
			}) ;
		}
	},
	/*
	Function initValue kita gunakan untuk mengembalikan Value dari element Input ke defaultValue ( Value yang kita isikan di txt::)
	Syaratnya harus Dari Component kita dan field yang kita reset hanya component text,radio,checkbox,password,number,date
	Selain itu untuk component hidden,button tidak kita reset
	*/
	initValue(_frm,vaIgnoreField){
		if(typeof _frm == "undefined" || _frm == "") _frm = a.f ;
		if(typeof _frm !== "undefined"){
			const cIgnore = typeof vaIgnoreField !== "undefined" ? "," + vaIgnoreField.join(",") + "," :",," ;
			const type = ",text,radio,checkbox,password,number,date,file," ;
			let oldName = "" ;
			for(let n=0;n<_frm.length;n++){
				// init Text
				const field = _frm.elements[n] ;
				txt.init(field) ;
				if(typeof txt.data[field.id] !== "undefined" && typeof txt.data[field.id].input.defaultValue !== "undefined"){
					if(type.indexOf("," + txt.data[field.id].obj.type + ",") >= 0 && cIgnore.indexOf("," + field.name + ",") < 0){
						if(txt.data[field.id].obj.type == "checkbox" || txt.data[field.id].obj.type == "radio"){
							// kita Check jika option pertama atau yang check kusus radio
							let lChecked = txt.data[field.id].input.checked ;
							if(txt.data[field.id].obj.type == "radio"){
								lChecked = txt.data[field.id].input.checked || field.name !== oldName ;
							}
							field.checked = lChecked ;
						}else{
							if(typeof field.value2 !== "undefined"){
								field.value2 = txt.data[field.id].input.defaultValue ;
							}else{
								field.value = txt.data[field.id].input.defaultValue ;
							}
						}
					}
				}
				oldName = field.name ;
			}
		}
		if(typeof tab !== "undefined") tab.init() ;
	},	
	/*
	Fungsi ini akan memasukkan Field Ke dalam component input didalam form kita tapi syaratnya harus memiliki nama yang sama contoh
	select Kode,Keterangan from agama 
	maka dia kalau di panggil akan menari field cKode dan cKeterangan
	Tetapi Component yang bisa di load hanya component yang dibuat via txt:: bukan <input> biasa dari html
	*/
	obj2Field(obj,vaIgnoreField){		
		const vaType = {"c":"text","n":"number","d":"date","opt":"radio","ck":"checkbox"} ;
		const cTypeList = ",text,number,date,radio,checkbox," ;
		const cIgnore = typeof vaIgnoreField !== "undefined" ? "," + vaIgnoreField.join(",") + "," : ",," ;
		let lFound = false ;
		
		for (let [key, value] of Object.entries(obj.getRow)) {
			lFound = false ;
			// Sebelum Kita Cek akan kita lihat apakah daftar Field nya ini masuk dalam fieldIgnore
			// Karena yang masuk dalam field Ignore boleh field entry atau field database contoh kita tuliskan cKode berarti field, kalau Kode berarti database
			if(cIgnore.indexOf("," + key + ",") >= 0){
				// Kalau dia masuk Field Ignore maka kita anggap Field Ketemu.
				lFound = true ;
			}else{
				for(const [pre,type] of Object.entries(vaType)){
					if(!lFound){
						let fields = document.getElementsByName(pre + key) ;
						if(fields.length > 0){
							// Hanya Type Data yang di bolehkan yang akan kita load text,number,date,radio,checkbox
							// Dan Hanya Component yang sudah kita definisikan menggunakan component assist
							if(typeof txt.data[fields[0].id].input !== "undefined" && typeof fields[0].Required !== "undefined"){
								if(cIgnore.indexOf("," + fields[0].name + ",") >= 0){
									// Kalau dia Jenisnya Field yang kita ignore maka tidak kita ambil valuenya, tapi dia kita anggap sudah ketemu
									lFound = true ;
								}else{
									if(cTypeList.indexOf("," + txt.data[fields[0].id].input.type + ",") >= 0){
										lFound = true ;
										if(type == "text" || type == "number" || type == "date"){
											if(typeof fields[0].value2 !== "undefined"){
												fields[0].value2 = value ;
											}else{
												fields[0].value = value ;
											}											
										}else if(type == "radio" || type == "checkbox"){
											SetOpt(fields,value) ;
										}
									}
								}							
							}							
						}
					}
				}
			}
			if(!lFound){
				console.log("Field",key,"not found") ;
			}
		}
	},
	_sms:{"callBack":{},lastCall:0,"sender":{},
				key:"",_id:0,
				get id(){
					if(frm._sms.key == ""){
						// Mendapatkan waktu saat ini
						const now = new Date();

						// Mengubah waktu menjadi format ddhhiimmss
						frm._sms.key = `${now.getDate()}${now.getHours()}${now.getMinutes()}${now.getSeconds()}${now.getMilliseconds()}`;
					}
					return "K" + frm._sms.key + "-" + frm._sms._id ;
				},
				set id(value){
					frm._sms._id ++ ;
					return frm._sms._id ;
				}
			 },
	sendMessage(data,cTarget="mainFrame",callFunc=null,callBack=null,vaPos=null,vaPar=null){
		/*
		Format Data
		data.data = berisi data yang dikirim
		data.par = Paramter
		*/
		if(typeof data == "object" && typeof data.data !== "undefined" && typeof data.par !== "undefined"){
			data.par.caller.push(window.name) ;
		}else{
			let lCallBack = callBack !== null ;
			frm._sms.id ++ ;
			if(lCallBack){
				frm._sms.callBack[frm._sms.id] = callBack ;
			}
			data = {data:data,par:{type:"post",call_id:frm._sms.id,target:cTarget,callFunc:callFunc,caller:[window.name],pos:vaPos,vaPar:vaPar,lCallBack:lCallBack,appid:svr.GetAppID()}} ;
		}
		data.par.lastCaller = window.name ;
		window.parent.postMessage(data, '*');
	},
	responseMessage(obj,caller,nCall_ID=null){
		if(nCall_ID == null) nCall_ID = frm._sms.lastCall ;

		// Waktu Response Message maka dia akan melihat caller yang berisi daftar Object pemanggil nya
		// Selama Caller lebih dari 0 berarti masih ada rangkaian Caller nya.
		if(caller.length > 0){
			caller.pop() ;				// Hapus Elamant Array Caller Terahir umpama [form1,form2,form3] dia akan menghapus form3
			let win = null ;
			if(typeof frm._sms.sender[nCall_ID] !== "undefined"){
				win = frm._sms.sender[nCall_ID] ;
				delete frm._sms.sender[nCall_ID] ;
			}

			if(win !== null){
				let data = {data:obj,par:{"type":"reponse","caller":caller,"call_id":nCall_ID}} ;
				win.postMessage(data, '*') ;
			} 
		}
	},
	funcPos:{top:0,left:0},			// Variable ini untuk menyimpan Posisi Form yang memanggil di hitung dari Layar.
	receiveMessage(event){
		const data = event.data ;
		/*
		Kita Akan Cek apakah data boleh di proses dengan syarat
		1. Same Origin kalau tidak
		2. Check Type Message apa kalau dia bukan Response Message maka kita lakukan check
		3. Kita cek apakah appid sama dengan posisi appid aktif
		*/
		let lValid = data.source !== "cloudflare-challenge" ; //cloudflare kita skip karena tidak bisa dicustom dan component tidak perlu melakukan apapun
		if(event.origin !== window.origin && lValid){
			const type = data.par.type ;
			// Kita Cek kalau bukan Type response dan responseChild maka dia Request ini yang akan kita check apakah data aman
			if(type !== "reponse" && type !== "responseChild"){
				const reqid = data.par.appid ;
				const appid = svr.GetAppID() ;
				if(reqid !== appid){
					console.error("Post Message Not Allowed ...!") ;
					lValid = false ;
				}
			}
		}
		/*
		Kalau Valid Kita proses Berikut nya
		*/
		if(lValid){
			if(data.par.type == "reponse"){				// Response
				// Reponse Kalau caller masih ada berarti kita akan foward caller berikut nya
				if(data.par.caller.length > 0){
					frm.responseMessage(data.data,data.par.caller,data.par.call_id) ;
				}else{
					// Kalau caller Kosong kita callback
					let id = data.par.call_id ;
					if(typeof frm._sms.callBack[id] !== "undefined" && frm._sms.callBack[id] !== null){
						let callBack = frm._sms.callBack[id] ;
						delete frm._sms.callBack[id] ;
						callBack(data.data,200) ;
					}
				}
			}else if(data.par.type === "extension"){
				if (data !== undefined && data !== null){
					if(data.data.type == "extCheck"){
						var deviceId   = data?.data?.device_id   || "";
						var access_token   = data?.data?.access_token   || "";
						a.ajax(svr.GetComponentPath()+"/cekdevice","CekDevice", "cDeviceID="+deviceId+"&cToken="+access_token, function (obj) {
							if(obj.data.cError == "reload"){
								location.reload() ;
								if(obj.data.cLogout != "login"){
									window.postMessage({data:{},par:{type: "extension",to: "logout"}}, location.origin);	
								}
							}
						});	
					}else if(data.data.type == "extReady"){
						setInterval(() => {
							window.postMessage(
								{ data:{}, par:{ type: "extension", to: "getDevice" } }, 
								location.origin
							);
						}, 30000); // 5000 ms = 15 detik
					}else{
						return true;
					}
				}else{
					return true;
				}
			}else if(data.data.type === "activity"){
			  window.top.idleSession.resetTimer();
			}else if(data.par.type == "callChild"){		// Call Dari Induk
				let callFunc = eval(data.par.callFunc) ;
				let vaPar = data.par.vaPar ;
				let retval = null ;
				if(typeof callFunc !== "function"){
					console.error("function name " + data.par.callFunc + " not found ....!") ;
				}else{
					// Kita Call Function nya.
					retval = callFunc(...vaPar) ;

					// Untuk CallFunc Kalau ada callBack maka otomatis kita Response 
					// dan Result akan kita jadikan parameter callback
					// Ini kita Reponse Untuk Call Child kita response ke parent nya / via event.source
					if(data.par.lCallBack){
						let resp = {data:retval,par:{type:"responseChild",call_id:data.par.call_id}} ;
						event.source.postMessage(resp, '*');
					}
				}
			}else if(data.par.type == "responseChild"){		// Response Ke Induk
				let id = data.par.call_id ;
				if(typeof frm._sms.callBack[id] !== "undefined" && frm._sms.callBack[id] !== null){
					let callBack = frm._sms.callBack[id] ;
					delete frm._sms.callBack[id] ;

					callBack(data.data,200) ;				
				}
			}else{																				// Request
				
				// Kalau Request Kita akan mencatat koordinat iframe nya biar kita bisa tahu berapa posisi field yang kita tuju
				if(data.par.pos == null) data.par.pos = {top:0,left:0} ;

				// Kita Akan Catat Koordinat iframe Caller, ini kita gunakan untuk menghitung berapa posisi Koordinat Field
				let id = data.par.caller[data.par.caller.length-1] ;
				let iframe = a.getById(id + "_formbody") ;
				if(iframe !== null){
					let rect = iframe.getBoundingClientRect() ;
					data.par.pos.top += rect.top ;
					data.par.pos.left += rect.left ;
				}
       
				// Jika ada Callback maka Kita Simpan sender untuk mengirim pesan Balasan
				// Jika Tidak ada CallBack kita tidak buat Jalur Kembalian.
				if(data.par.lCallBack){
					frm._sms.sender[data.par.call_id] = event.source ;
					
					// Kita akan buat setTimeOut Selama 2 Menit Untuk Menghapus Jalur kalau ada 
					setTimeout((par)=>{
						if(typeof frm._sms.sender[id] !== "undefined"){
							delete frm._sms.sender[id] ;
						}
					},120000,data.par.call_id) ;
				}

				// Kita Check Jika Target tidak sama dengan windows.name dan target tidak kosong maka kita lanjut ke parent
				// Kalau Window.name == root maka kita tidak akan callparent lagi karena itu sudah yang paling tinggi.
				frm._sms.lastCall = data.par.call_id ;
				if(window.name !== "root" && data.par.target !== "" && window.name !== data.par.target){
					// Kalau Ketemu window.name == mainFrame maka target kita kosongkan biar dia hanya mengambil 1 level di atasnya
					// karena dia dianggap root yaitu parent nya mainFrame
					if(window.name == "mainFrame") data.par.target = "" ;
					frm.sendMessage(data,data.par.target) ;
				}else{
					let callFunc = eval(data.par.callFunc) ;
					let vaPar = data.par.vaPar ;
					frm.funcPos = data.par.pos ;
					
					if(vaPar !== null){			// Dipanggil Via frm.callFunc
						let retval = null ;
						if(typeof callFunc !== "function"){
							console.error("function name " + data.par.callFunc + " not found ....!") ;
						}else{
							retval = callFunc(...vaPar) ;
						}
						//console.log("func",data.par.callFunc," retvael",retval) ;
						// Untuk CallFunc Kalau ada callBack maka otomatis kita Response 
						// dan Result akan kita jadikan parameter callback
						if(data.par.lCallBack){
							frm.responseMessage(retval,data.par.caller,data.par.call_id) ;
						}else{
							// Kalaupun tidak ada Response tetap kita hapus sender biar tidak bertumpuk
							let id = data.par.call_id ;
							if(typeof frm._sms.sender[id] !== "undefined"){
								delete frm._sms.sender[id] ;
							}
						} 
					}else{								// Dipanggil via frm.sendMessage
						callFunc(data) ;
					}
				}
			}
		}
	},
	/*
	Kita gunakan callFunc kalau memanggil Child tapi hanya bisa 1 level saja
	*/
	callChildFunc(callFunc,vaPar=null,cTarget="",callBack=null,nTimeout=0){
		if(!Array.isArray(vaPar)) vaPar = [vaPar] ;		

		/*
		callFunc = Function yang akan kita Call
		vaPar = Parameter Function Dalam Array
		cTarget = Iframe Chiled Target
		*/
		let win = null ;	
		let _main = window ;
		
		// Kita akan cari iframe di windows Aktif
		if(cTarget !== "mainFrame") cTarget += "_formbody" ;
		let iframe = a.getById(cTarget,_main) ;
		
		// Jika iframe tidak ketemu dan Call Dari root maka kita akan cari iframe mainFrame karena berikutnya ada di bawah iframe		
		if(iframe == null && _main.name == "root"){			
			_main = a.getById("mainFrame") ;
			if(_main !== null) _main = _main.contentWindow ;

			iframe = a.getById(cTarget,_main) ;		
		}		
		
		if(iframe !== null) win = iframe.contentWindow ;		
		if(iframe !== null){
			let lCallBack = callBack !== null ;
			
			// Jika Callback Akan kita simpan function Callbacknya
			frm._sms.id ++ ;
			if(lCallBack) frm._sms.callBack[frm._sms.id] = callBack ;

			// Kalau kita pasang Timeout maka kita buatkan settimeout untuk mendeteksi 
			// Kalau callBAck[id] tidak kosong maka dia belum di call, karena timeout maka kita call dengan status 408
			if(lCallBack && nTimeout > 0){
				setTimeout((id)=>{
					if(typeof frm._sms.callBack[id] !== "undefined"){
						frm._sms.callBack[id](null,408) ;
					}
				},nTimeout,frm._sms.id) ;
			}

			data = {data:{},par:{type:"callChild",call_id:frm._sms.id,callFunc:callFunc,vaPar:vaPar,lCallBack:lCallBack,appid:svr.GetAppID()}} ;
			win.postMessage(data, '*');
		}
	},
	/*
	Kita gunakan untuk memanggil Fucntion pada Form parent, walaupun cross origin.
	*/
	callFunc(cFuncName,vaPar,cTarget="",callBack=null,nTimeout=0){
		let data = [] ;
		if(!Array.isArray(vaPar)) vaPar = [vaPar] ;
		if(cTarget !== "" && cTarget == window.name){
			
			// Kalau Target tidak kosong, dan nama target sama dengan window.name maka langsung kita call function nya.
			let callFunc = eval(cFuncName) ;
			let retval = null ;
			if(typeof callFunc !== "function"){
				console.error("function name " + data.par.callFunc + " not found ....!") ;
			}else{
				retval = callFunc(...vaPar) ;
			}

			// Untuk CallFunc Kalau ada callBack maka otomatis kita Response 
			// dan Result akan kita jadikan parameter callback
			if(callBack !== null){
				callBack(retval) ;
			} 
		}else{
			frm.sendMessage(data,cTarget,cFuncName,callBack,null,vaPar) ;

			// Jika di pasang Timeout Dan ada call back maka kita tunggu disini
			if(callBack !== null && nTimeout > 0){
				setTimeout((id)=>{
					if(typeof frm._sms.callBack[id] !== "undefined"){
						let callBack = frm._sms.callBack[id] ;
						delete frm._sms.callBack[id] ;

						callBack(null,408) ;
					}
				},nTimeout,frm._sms.id) ;				
			}
		}
	},
	isAllowedPage(){
		/*
		Kita akan cek apakah dia ada __token dan appid kalau ada maka dia hanya bisa dibuka di iframe
		*/
		const params = new URLSearchParams(window.location.search);
		let token = params.get("__token") ;
		let appid = params.get("appid") ;
		if(token !== null || appid !== null){
			if(self.parent == window){
				// Kalau dia di buka bukan di Iframe dan memiliki token dan appid maka kita block tidak bisa buka form
				document.body.innerHTML = ""; // Mengosongkan konten website
				document.body.appendChild(document.createTextNode("Anda tidak memiliki hak membuka halaman ini")); // Menampilkan pesan
			}
		}
	},
}
window.addEventListener('message', (event)=>{frm.receiveMessage(event) ;});

// Tool Bar
const tBar = {
  vatBar:{},nCell:0,oDiv:null,Name:"",
  show: function(cName,nTop,nLeft,nHeight,nWidth,parent){
		frm.SaveLog("02","Load Home / Refresh Program") ;
		
		if(nTop == null) nTop = 0 ;
    if(nLeft == null) nLeft = 0 ;
    if(nHeight == null) nHeight = "auto" ; //24 ;
    if(nWidth == null) nWidth = "100%" ;
    
    tBar.oDiv = a.addObj("div",parent,cName,"tbar_main") ;
    with(tBar.oDiv.style){
      top = nTop ;
      left = nLeft ;
      width = nWidth ;
    }

    for(let key in tBar.vatBar){
      let ele = tBar.vatBar [key] ;
      if(ele ['name'] == "--separator--"){
        let o = a.addObj("div",tBar.oDiv,null,"tbar_item_sep no_txt_select") ;
      }else{
        let o = a.addObj("div",tBar.oDiv,ele ['name'] + "-toolBar-div","tbar_item_div") ;

				let img = null ;
        if(ele ['img'] == ""){
          img = a.addObj("div",o,null,"tbar_item_txt no_txt_select","height:20px",ele ['title']) ;
        }else{
          img = a.addObj("img",o,null,"tbar_item no_txt_select","width:20px;height:20px") ;
          img.src = ele['img'] ;
        }
        img.title = ele ['title'] ;
        img.id = ele['name'] + "-toolBar-Item" ;

        (function(ele){
          img.onclick = function(){
						//frm.SaveLog("03",ele ['title']+" - AppBar") ;
						if(!txt.canClick(img)) return false ;
						if(ele ['callBack']) ele ['callBack']()
					} ;
        })(ele) ;
        let nTop = Math.max((tBar.oDiv.offsetHeight - img.offsetHeight) / 2,0) ;
        if(ele ['img'] !== "") img.style.marginTop = 2 ;
      }
    }

    tBar.vatBar = {} ;
  },
  add: function(cName,title,img,callBack){
    tBar.nCell ++ ;
    tBar.vatBar [tBar.nCell] = {name:cName,title:title,img:img,callBack:callBack} ;
  },
  addSep: function(){
    tBar.nCell ++ ;
    tBar.vatBar [tBar.nCell] = {name:"--separator--"} ;
  },
  getItem(cItem){
    return a.getById(cItem + "-toolBar-Item") ;
  }
};

// Class Status Bar
const sBar = {
  vasBar:{},nCell:0,oDiv:null,Name:"",
  show: function(cName,parent){
		let vaBar = {} ;
    let nWidth = "100%" ;
    
    sBar.ot = a.addObj("table",parent,cName,"sbar_main") ;
    sBar.ot.width = nWidth ;

    sBar.oDiv = a.addObj("tr",sBar.ot) ;
    for(const key in sBar.vasBar){
      let ele = sBar.vasBar [key] ;

      let o = a.addObj("td",sBar.oDiv,ele ['name'] + "-cell-content-","sbar_item no_txt_select",null,ele ['title']) ;
			o.style.height = "20px" ;
      if(ele ['width'] !== null) o.style.width = ele ['width'] ;
      if(ele ['align'] !== null) o.style.textAlign = ele ['align'] ;
			vaBar[ele['name']] = o ;
    }

    sBar.vasBar = {} ;
		return vaBar ;
  },
  add: function(cName,title,width,align){
    sBar.nCell ++ ;
    sBar.vasBar [sBar.nCell] = {name:cName,title:title,width:width,align:align} ;
  },
  getItem(cItem){
    return a.getById(cItem + "-cell-content-") ;
  }
};