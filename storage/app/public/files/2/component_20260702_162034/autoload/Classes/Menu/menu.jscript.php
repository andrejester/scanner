<script language="javascript" type="text/javascript">
const menu = {
	conf:{token:""},
	va:{
		conf:null,
		// cell = berisi div.submenu, oldCell = yang terakhir di over
		hz:{click:false,cell:null,oldCell:null},
		back:null,url:"",
		vr:{},		// Variable menampun menu vertical nanti berisi v[nLevel] = {}
	},
	// Pada saat Menu Vertical Di Click
	vrMenuClick(divRow){
		let item = divRow.obj.item ;
		this.closeAllMenu() ;

		// Jika url == null atau # maka tidak kita jalankan Kecuali Memiliki submenu
		if(item.url == null || item.url.toLowerCase() == "null" || item.url.toLowerCase() == "#") return true ;
		
		/*
		Kita Akan SaveLog Untuk Open Menu nya
		*/
		/*
		let urlLog = svr.GetComponentPath() + "/menu/mmenu.ajax.php" ;
		a.ajax(urlLog,"MenuSaveLog","cMenuNumber=" + item.mnuNumber + "&cMenuTitle="+item.mnuTitle,(obj)=>{
			
		}) ;
		*/
		//a.ajax(svr.GetComponentPath() + "/savelog","SaveLog()","cMTI=03&cMessage="+item.mnuTitle+" - "+item.mnuNumber) ;
		
		/*
		Menjalankan Menu ada cara :
		1. Check kalau host !== localhost maka sub module selain itu bukan sub module
		1. Kalau ada mnuFunc_onClick maka kita call function nya
		2. Kita Open Form 
		*/
		let lHTTP = item.url.substr(0,7) == "http://" || item.url.substr(0,8) == "https://" ;
		if(item.host !== "localhost" || lHTTP){
			// Sub Module
			let title = item.frmTitle + " - Menu : " + item.mnuNumber ;

			let vaPos = {} ;
			let url = item.url ;
			if(!lHTTP){
				if(item.sub_mvc == "MVC"){
					url = item.host + item.url ; // + "/?";
				}else{
					url = item.host + "/main.php" ; 
					vaPos.__par = item.url ;
				}				
				vaPos.Mnu = item.mnuNumber ;
				vaPos.MnuTitle = title ;
			}
			frm.open({url:url,POST:vaPos},item.mnuID,title,item.frmWidth,item.frmHeight,'',false,'no',false,'mainFrame','',item.mnuNumber,item.buttonListAllowed) ;
		}else{
			if(eval("typeof " + item.mnuFunc + "_onClick") == "function"){
				eval(item.mnuFunc + "_onClick(this,'" + item.mnuID +"')") ;
			}else{
				let url = svr.IsMVC() ? item.url : "main.php?__par=" + item.url.replace("?", "&") ;
				frm.open(url,item.mnuID,item.frmTitle + " - Menu : " + item.mnuNumber,item.frmWidth,item.frmHeight,'',false,'no',false,'mainFrame',false,item.mnuNumber,item.buttonListAllowed) ;
			}
		}
	},
	show(){
		let divMain,divCell = null ;
		divMain = a.getById("_div_mnu_pulldown_") ;
		if(divMain !== null){
			let obj = a.str2JSON(divMain.textContent) ;
			obj = obj.getRow ;
			menu.conf = obj.conf ;
			
			// Kalau ada menu horizontal maka kita anggap sebagai root
			window.name = "root" ;

			//retract dari cookie ke dom
			// Dapatkan cookie Save di svr
			//let token = svr.GetCookie("token") ;
			//frm.callFunc("svr._saveToken",[token],"root") ;
			//svr._appid = svr.GetCookie("appid") ;
			
			// Token akan kita Simpan di svr._saveToken
			frm.callFunc("svr._saveToken",[menu.conf.token],"root") ;
			svr._appid = menu.conf.appid ;
			//end retract dari cookie ke dom
	
			obj = obj.menu ;
			this.va.url = svr.GetComponentURL() + "autoload/Classes/Menu" ;
			divMain.innerHTML = "" ;
			divMain.className = "menu2_hrz_main" ;
			divMain.style = "position:absolute;top:0px;left:0px;display:block;height: auto;width:100%" ;
			this.va.divMain = divMain ;
			
			//obj["bookmark"] = {"item":{mnuNumber:"",mnuTitle:"📁 Bookmarks"}}
			for(var key in obj){
				divCell = a.addObj("div",divMain,null,"menu2_hz",null," " + obj[key].item.mnuNumber + " " + obj[key].item.mnuTitle + " ") ;
				divCell.mnuType = "hzMenu" ;
				if(key == "bookmark"){
					divCell.style = "float:right;margin-right:2px" ;
					divCell.mnuType = "bookmark" ;
				}
				divCell.cOut = "menu2_hz" ;
				divCell.cOver = "menu2_hz_over" ;
				((divCell,mnu)=>{
					divCell.onmouseover = function(){
						// Kita Ceck Apakah ada Cell Lama Kalau ada maka class kita jadikan Out						
						if(mnu.va.hz.oldCell !== null) mnu.va.hz.oldCell.className = mnu.va.hz.oldCell.cOut ;

						// Class Kita Ganti Ke Over
						mnu.va.hz.oldCell = divCell ;
						divCell.className = divCell.cOver
						if(mnu.va.hz.click){
							mnu.hzMenuClick(divCell);
						}
					} ;
					divCell.onmouseout = function(){
						if(!mnu.va.hz.click) divCell.className = divCell.cOut
					} ;
					divCell.onclick = function(){mnu.hzMenuClick(divCell);} ;
				})(divCell,this) ;
				if(typeof obj[key].subMenu !== "undefined") this.createSubMenu(obj[key].subMenu,divCell,1) ;
			}

			// Kita Akan cari Parent untuk menempatkan Menu Horizontal dengan kriteria
			// 1. Cari cell obj dengan id cParentID
			// 2. Kalau tidak ketemu kita cari iframe = mainFrame dan kita cari induk dari ifram itu, karena dia biasanya menggunakan table.
			//    Kalau ketemu tag table induknya iframe maka kita akan ambil cell pertama, dan itu yang kita gunakan untuk parent 
			if(typeof this.conf.cParentID !== "undefined"){
				let tdParent = a.getById(this.conf.cParentID) ;
				
				// Kalau Tidak Ketemu id cParentID maka kita ambil ifram mainFrame dan kita ambil table parent nya
				// setelah itu ambil <tr> dan <td> pertama
				if(tdParent == null){
					let iframe = a.getById("mainFrame") ;
					if(iframe !== null){
						let table = iframe.closest("TABLE");
						if(table !== null && table.rows.length > 0 && table.rows[0].cells.length > 0){
							tdParent = table.rows[0].cells[0] ;
						}
					}
				}

				if(tdParent !== null){
					tdParent.innerHTML = "" ;
					tdParent.appendChild(divMain) ;
					divMain.style.position = "relative" ;
				} 
			}

			// Kita Buat Background 
			let nTop = divMain.offsetTop + divMain.offsetHeight ;
			if(this.va.back == null){
				this.va.back = a.addBack("idBackMainMenu") ;
				this.va.back.style = "display:none;opacity:0" ;
				((mnu,back)=>{
					 back.onclick = ()=>{mnu.closeAllMenu()} ;
				})(this,this.va.back) ;
			}
		}
	},
	closeAllMenu(){
		this.va.hz.click = false ;
		this.va.back.style.display = "none" ;
		this.va.hz.cell.style.display = "none" ;
		if(this.va.hz.oldCell !== null) this.va.hz.oldCell.className = this.va.hz.oldCell.cOut ;		// Menu Horizontal kita mouseOut Semua
		this.closeSubMenu(0) ;
	},
	hzMenuClick(divCell){
		// Menu Lama Kita Tutup
		if(typeof this.va.hz.cell !== "undefined" && this.va.hz.cell !== null) this.va.hz.cell.style.display = "none" ;
		this.closeSubMenu(1) ;

		// Atur sub Menu
		this.va.hz.click = true ;
		this.va.hz.cell = divCell.subMenu ;
		if(divCell.mnuType == "bookmark"){

		}else if(divCell.subMenu !== "undefined"){
			this.setMenuPos(divCell.subMenu,divCell,"bottom") ;	// Atur Kalau Menu telalu tinggi atau terlalu ke kanan
		}	
	},
	// Kalau Roda Mouse di Scroll maka menu akan kita scroll juga
	whellScroll(div,par){
		if(typeof div.isScroll !== "undefined" && div.isScroll){
			div.scrollTop += par ;
			menu.setupScrollButton(div) ;
		}
	},
	setupScrollButton(div){
		//Jika Posisi ScrollTop < ScrollHeight maka tombol scroll Bottom Tampilkan
		if(div.scrollTop + div.clientHeight < div.scrollHeight - 2){
			div.divDown.style.display = "block" ;
		}else{
			div.divDown.style.display = "none" ;
			div.scrollTop = div.scrollHeight - div.clientHeight - 2;
		}
		
		// Jika ScrollTop > 0 makak divScrollUp Tampilkan
		if(div.scrollTop > 0){
			with(div.divUp.style){
				display = "block" ;
				width = div.clientWidth ;
				top = div.getBoundingClientRect().top ;
				left = div.left ;
			}
		}else{
			div.divUp.style.display = "none" ;
		}
	},
	// Untuk Menyusun Sub Menu, Untuk Menu Vertical nya
	// obj = Daftar object
	// cell = item menu
	// nLevel = Level Vert Menu kita gunakan untuk menentukan level menu yang di buka 
	//          Jadi kalau kita pindah menu contoh level1 maka semua sub menu level 2 ke atas akan kita close
	createSubMenu(obj,cell,nLevel){
		let divMain,divRow,divCell,iconSub,title,divLeft = null ;
		divMain = a.addObj("div",null,null,"menu2_vert_main","display:none") ;		
		((div)=>{
			div.onwheel = (e)=>{
				let par = e.deltaY > 0 ? 28 : -28 ;
				menu.whellScroll(div,par) ;
			} ;
		})(divMain) ;

		// Membuat Background kiri menu
		divLeft = a.addObj("div",divMain,null,"menu2-left") ;

		divMain.divLeft = divLeft ;
		cell.subMenu = divMain ;
		for(var key in obj){
			divRow = a.addObj("div",divMain,null,"menu2_vert_item") ;
			divRow.cOut = "menu2_vert_item" ;
			divRow.cOver = "menu2_vert_item menu2_vert_item_over" ;
			divRow.nLevel = nLevel ;
			divRow.obj = obj[key] ;
			title = "" ;
			divRow.icon = {"type":"","value":"","valueOver":"","data":null} ;
			iconSub = "" ;
			// Atur Event divRow
			if(obj[key].item.mnuTitle !== "-"){
				((divRow,mnu)=>{
					// Menu Over untuk Menu Vertical
					divRow.onmouseover = function(){
						divRow.className = divRow.cOver ;

						// Menu Lama Kita Tutup
						// Kalau dia punya Sub Menu maka kita hapus di bawah nya
						if(typeof mnu.va.vr[divRow.nLevel] !== "undefined"){
							// Kita Cek MenuNumber kalau sama berarti posisi cursor tidak pindah menu
							// Berarti tidak usah kita close sub menunya
							if(divRow.mnuNumber !== mnu.va.vr[divRow.nLevel].oldCell.mnuNumber){
								mnu.va.vr[divRow.nLevel].oldCell.className = divRow.cOut ;

								// Icon menu lama juga kita ganti
								mnu.setMnuIcon(mnu.va.vr[divRow.nLevel].oldCell,false) ;

								mnu.closeSubMenu(divRow.nLevel) ;
							}							
							mnu.va.vr[divRow.nLevel].oldCell = divRow ;
						} 

						// Atur Icon Sebelah kiri untuk Animasi
						mnu.setMnuIcon(divRow,true) ;

						// Kalau Dia Memiliki Sub Menu Maka kita buka Sub Menunya
						if(typeof divRow.subMenu !== "undefined" && divRow.subMenu.style.display !== "block") mnu.openSubMenu(divRow) ;
					}

					// Mouse Out
					divRow.onmouseout = ()=>{
						let lClick = false ;
						if(typeof mnu.va.vr[divRow.nLevel] !== "undefined"){
							lClick = mnu.va.vr[divRow.nLevel].click ;
						} 
						if(!lClick){
							// Atur Icon Sebelah kiri untuk Animasi
							mnu.setMnuIcon(divRow,false) ;

							divRow.className = divRow.cOut ;
						} 
					}

					// Menu Click Jika Tidak memiliki Submodul baru kita eksekusi Menu nya.
					divRow.onclick = ()=>{
						if(typeof divRow.subMenu == "undefined") mnu.vrMenuClick(divRow);
					}
				})(divRow,this) ;
				divRow.mnuNumber = obj[key].item.mnuNumber ;
				title = obj[key].item.mnuNumber + "&nbsp;&nbsp;" + obj[key].item.mnuTitle ;

				// Kita Cari kalau dia Ada icon kita tampilkan sebelah kirim Menu Vertical
				if(obj[key].item.icon !== ""){
					var ico1 = "" ;
					var ico2 = "" ;
					if(typeof obj[key].item.icon == "object"){
						var ico = obj[key].item.icon ;
						// Jika Panjang array 
						// 1. berarti [file] ;
						// 2. berarti [file1,file2] ;
						// 3. berarti [dir,file1,file2] ;
						if(ico.length == 1){
							ico1 = ico[0] ;
							ico2 = ico[0] ;
						}else if(ico.length == 2){
							ico1 = ico[0] ;
							ico2 = ico[1] ;
						}else{
							ico1 = ico[0] + ico[1] ;
							ico2 = ico[0] + ico[2] ;
						}
					}else{
						ico1 = obj[key].item.icon ;
						ico2 = obj[key].item.icon ;
					}					
					// Jika Jenis Img kita buat obj Image
					if(this.isImageFile(ico1)){
						var url = svr.IsMVC() ? svr.GetBaseURL() : "" ;
						divRow.icon.type = "img" ;
						divRow.icon.value = url + ico1 ;
						divRow.icon.valueOver = url + ico2 ; 
					}else{
						divRow.icon.type = "div" ;
						divRow.icon.value = ico1 ;
						divRow.icon.valueOver = ico2 ; 
					}
				}

				// Jika Memiliki Sub Menu Maka akan kita beri tanda OpenFolder dan Tanda SubMenu
				if(typeof obj[key].subMenu !== "undefined"){
					// Jika Icon kosong dan dia memiliki sub menu maka akan kita isi openfolder 🗁 🗀 📂 📁
					if(divRow.icon.type == ""){
						divRow.icon.type = "img" ;
						divRow.icon.value = this.va.url + "/images/menu-folder.png" ;
						divRow.icon.valueOver = this.va.url + "/images/menu-folder-open.png" ;
					} 
					iconSub = "<img style='margin:9px' src='" + this.va.url + "/images/arrow.gif?_th=auto'>" ; //">" ; 
				}
				divRow.Type = "menu" ;
			}else{
				divRow.style.height = "0px" ;
				divRow.className = "menu2_vert_item_sep" ;
				divRow.Type = "separator" ;
			}

			// Kolom Icon
			divCell = a.addObj("div",divRow) ; 
			if(divRow.icon.type !== ""){				
				divRow.icon.data = a.addObj(divRow.icon.type,divCell) ;
				divRow.icon.data.style = divRow.icon.type == "img" ? "width:18px;height:18px;padding-top:4px;margin-left:-4px" : "font-size:15px;text-align:center;margin-left:-6px" ;
				((img)=>{
					img.onerror = ()=>{
  					img.style.display = 'none';
					};
				})(divRow.icon.data) ;
				divRow.icon.data.alt = "" ;
			} 

			// Title
			divCell = a.addObj("div",divRow,null,null,"white-space: nowrap;padding-left:6px;padding-right:20px",title) ;

			// Item Kanan
			divCell = a.addObj("div",divRow,null,null,null,iconSub) ;

			// Jika Memiliki Sub Menu kita buat submenunya
			if(typeof obj[key].subMenu !== "undefined") this.createSubMenu(obj[key].subMenu,divRow,nLevel+1) ;
		}
	},
	isImageFile(filename) {
		const ext = filename.split('.').pop().toLowerCase();
		const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'tiff', 'ico'];
		return imageExtensions.includes(ext);
	},
	closeSubMenu(nLevel){
		for(const key in this.va.vr){
			if(key >= nLevel){
				this.va.vr[key].cell.style.display = "none" ;
				this.va.vr[key].click = false ;
			}
		}
	},
	// Kita Gunakan untuk Mengatur Posisi Menu yang di buka, untuk Atas dan Kiri nya
	setMenuPos(div,parent,pos="bottom"){
		// Kita lihat kalau div tidak terdefinisi berarti submenu tapi tidak ketemu susunan menunya
		if(typeof div == "undefined") return null ;

		// tombol Scroll Kita hilangkan dulu biar tinggi menu tidak pengaruh
		if(typeof div.divDown !== "undefined") div.divDown.style.display = "none" ;
		if(typeof div.divUp !== "undefined") div.divUp.style.display = "none" ;

		let sc = this.va.divMain.getBoundingClientRect() ;
		let scTop = sc.bottom ;
		let scBottom = document.body.clientHeight - 20 ;
		let scHeight = scBottom - scTop  ;
		let scWidth = document.body.clientWidth - 5 ;
	
		// Jika Baru Pertamakali di Click
		if(this.va.back.style.display !== "block"){
			this.va.back.style.display = "block" ;
			a.setObjIndex(this.va.back) ;
		}
		with(this.va.back.style){
			top = scTop ;
			left = 0 ;
			width = document.body.clientWidth ;
			height = document.body.clientHeight - scTop ;
		}

		// kita atur dulu posisi div yang seharusnya dengan rincian sbb
		// div = object sub menu yang mau di buka
		// parent = induk yang membuka sub menu
		// pos = "bottom" / "right" => posisi di buka sebelahmana dari parent untuk div nya
		let recParent = parent.getBoundingClientRect() ;

		let nTop = recParent.bottom ;
		let nLeft = recParent.left ;
		let nParentLeft = recParent.left ;
		if(pos == "right"){
			nTop = recParent.top - 3 ;
			nLeft = recParent.right ;
		}
		div.isScroll = false ;
		with(div.style){
			display = "block" ;
			height = "auto" ;
			width = "auto" ;
			overflow = "hidden" ;
			top = 0 ;
			left = 0 ;
			opacity = "0%" ;
		} 
		div.scrollTop = 0 ;
		this.initMenuItem(div) ;		// Kita buat semua classname ke out 
		
		// Ini adalah Menu Kiri yang background itu kita buat tinggi sama dengan divMain
		if(typeof div.divLeft !== "undefined"){
			div.divLeft.style.height = div.offsetHeight ;
		}
		
		let divHeight = div.offsetHeight ;
		let divTop = nTop ;
		let divLeft = nLeft ;
		let divWidth = div.offsetWidth ;

		// kita akan hitung kalau divTop+divHeight maka kita akan naikkan ke tas posisi window
		if(divHeight > scHeight){
			//div.style.overflowY = "scroll" ;
			div.style.height = scHeight ;
			nTop = scTop ;
			div.isScroll = true ;
			div.isOver = false ;
			if(typeof div.divDown == "undefined"){
				div.divDown =	a.addObj("div",div,null,"divMenuScroll-Down") ;
				img = a.addObj("img",div.divDown,null,null,"width:18px;height:18px") ;
				img.src = this.va.url + "/images/scroll-down.gif?_th=auto" ;
				((div)=>{
					div.divDown.onmousemove = ()=>{
						div.isOver = true ;
						menu.divOver(div,1) ;
					}	
					div.divDown.onmouseout = ()=>{
						div.isOver = false ;
					}
				})(div) ;
			}
			
			if(typeof div.divUp == "undefined"){
				div.divUp =	a.addObj("div",div,null,"divMenuScroll-Up") ;
				img = a.addObj("img",div.divUp,null,null,"width:18px;height:18px") ;
				img.src = this.va.url + "/images/scroll-up.gif?_th=auto" ;
				((div)=>{
					div.divUp.onmousemove = ()=>{
						div.isOver = true ;
						menu.divOver(div,-1) ;
					}				
					
					div.divUp.onmouseout = ()=>{
						div.isOver = false ;
					}
				})(div) ;
			} 
			menu.setupScrollButton(div) ;
		}else if(divTop + divHeight > scBottom ){
			nTop = scBottom - divHeight ;
		}

		// Atur Posisi Kiri Menu, Kalau terlalu Kekanan maka geser Ke kiri
		if(divLeft + divWidth > scWidth){
			// Kita akan geser di sebelah Kiri dari Induk, dan Minimum nLeft = 0 ;
			nLeft = Math.max(1,nParentLeft - divWidth) ;
		}
		div.style.left = nLeft ;
		div.style.top = nTop ;
		div.style.opacity = "93%" ;
		a.setObjIndex(div) ;
	},
	divOver(div,par){
		menu.whellScroll(div,par) ;
		if(div.isOver){
			setTimeout(menu.divOver,40,div,par) ;
		}
	},
	openSubMenu(divRow){
		let div = divRow.subMenu ;
		let nLeft,nTop = 0 ;

		// Menu kita simpan sesuai Level
		this.va.vr[divRow.nLevel] = {click:true,cell:div,oldCell:divRow} ;
		this.setMenuPos(div,divRow,"right") ;		// Buka Dan Atur Posisi Sub Menu
	},
	initMenuItem(div){
		let rows = div.querySelectorAll(".menu2_vert_item");
		for(const row of rows){
			if(row.Type == "menu") row.className = row.cOut ;

			this.setMnuIcon(row,false) ;
		}
		return true ;
	},
	setMnuIcon(divCell,lOver){
		// Kita Atur Colom Pertama Iconnya kalau ada kita ganti isi nya
		if(divCell.icon.type == "div"){
			divCell.icon.data.innerHTML = lOver ? divCell.icon.valueOver : divCell.icon.value ;
		}else if(divCell.icon.type == "img"){
			if(divCell.icon.data.style.display !== "none"){
				let src = lOver ? divCell.icon.valueOver : divCell.icon.value ;
				if(src !== divCell.icon.data.src) divCell.icon.data.src = src ;
			} 
		}
	}
}
window.addEventListener('load', function() {
	menu.show() ;
});
</script>