window.addEventListener('load', function(){a.init()}, false ) ;
const a = {
	queue: Promise.resolve(), // Mulai dengan Promise yang sudah selesai
  g:function(){return _grandWin()},
  Browse: function(cSQL,field,callBack){
		txtBtn.Open(cSQL,field,callBack) ;
	},
  mnuClick:"",
	get gWin(){return _grandWin()},
  f: document.form1,
  init: function(){
    this.f = document.form1 ;
  },
	OpenIcon(imgName){
		imgName = svr.GetComponentURL() + "/themes/icons/global/" + imgName ;
		return imgName ;
	},
	// Untuk Obj Index kita jadikan satu semua harus di ambil dari mainFrame
	setObjIndex(_obj=null){
		let nIndex = gData.Get("objIndex",0) ;
		if(_obj == null) return nIndex ;		// Kalau Object Kosong maka dia meminta info posisi objIndex Terakkhir

		if(typeof _obj.style.zIndex == "undefined" || _obj.style.zIndex == "" || nIndex < 0 || _obj.style.zIndex < nIndex){
			_obj.style.zIndex = gData.Save("objIndex",++nIndex) ;
		}
		return nIndex ;
	},
  keyNum: function(e){
    let keynum = 0 ;
    if(window.event){ // IE
      keynum = e.keyCode ;
    }else if(e.which){ // Netscape/Firefox/Opera
      keynum = e.which ;
    }
    return keynum ;
  },
  alert: function(cMessage,title,callback){
		let data = {type:"Alert",message:cMessage,title:title,postMessage:true} ;
		frm.sendMessage(data,"root","msg._show",callback) ;
  },
  confirm: function(cMessage,title,callback){
		let data = {type:"Confirm",message:cMessage,title:title,postMessage:true} ;
		frm.sendMessage(data,"root","msg._show",callback) ;
  },
  prompt: function(cMessage,cDefault,callback){
		let data = {type:"Prompt",message:cDefault,title:cMessage,postMessage:true} ;
		frm.sendMessage(data,"root","msg._show",callback) ;
  },
  wait: function(nTimeout,title){
    return msg.waitStart(nTimeout,title) ;
  },
  endwait: function(){
    msg.waitEnd() ;
  },
  delObj:function(objs){
		if(!Array.isArray(objs)) objs = [objs] ;
		for(const obj of objs){
			if(obj !== null && obj.parentNode !== null) obj.parentNode.removeChild(obj) ;
		}
  },
  delById:function(cID,parent=null){
    let oDiv = a.getById(cID,parent) ;
    this.delObj(oDiv) ;
  },
  getById(id,parent){
    if(parent == null) parent = document ;
    let o = null ;
    if(parent.nodeType == 9){			// Type Document
      o = parent.getElementById(id) ;
		}else if(typeof parent.document !== "undefined"){
			o = parent.document.getElementById(id) ;
    }else{
      for(var node=0;node<parent.childNodes.length;node++){
        if(parent.childNodes [node].id == id) o = parent.childNodes [node] ;
      }
    }
    return o ;
  },
  addObj(cType,oParent=null,cID=null,cClassName=null,cStyle=null,cInnerHTML=null,cSrc=null){
    if(oParent == null){
			oParent = document.body ;
		}else if(typeof oParent.document !== "undefined"){
			oParent = oParent.document.body ;
		}
    let o = document.createElement(cType) ;
    oParent.appendChild(o) ;

    if(cID !== null) o.id = cID ;
    if(cClassName !== null) o.className = cClassName ;
		if(cStyle !== null) o.style = cStyle ;
		if(cInnerHTML !== null) o.innerHTML = cInnerHTML ;
		if(cSrc !== null) o.src = cSrc ;

    return o ;
  },
	addObjById(id,type="div",parent=null,cClassName=null,cStyle=null,cInnerHTML=null){
		let div = a.getById(id,parent) ;
		if(div == null) div = a.addObj(type,parent,id,cClassName,cStyle,cInnerHTML) ;
		return div ;
	},
	mvcURL(cKey=""){		// kita gunakan untuk mengambil url kalau dia mvc kalau pathname kosong maka kita ganti /home/ karena key ada method
		let url = svr.GetBaseURL() ;		// Ambil Base URL nya 
		let path = window.location.origin + window.location.pathname ;   // Path kita ambil url penuh kita kurangi dengan base url
		path = path.substring(url.length-1) ;
		if(path == "" || path == "/"){
			path = "/home/" ;
		}
		if(url.substring(url.length-1) == "/") url = url.substring(0,url.length-1) ;
		if(path.substring(0,1) !== "/") path = "/" + path ;

		url += path ;
		url += url.substring(url.length - 1) == "/" ? "" : "/" ;
		url += cKey ;
		return url ;
	},
	var2Data(_var){
		return a.str2JSON(_var) ;
	},
	str2JSON(str,status=0) {
		let obj = null ;
		let va = {"data":"","dataRows":0,"dataType":"string","src":str,"status":status} ;
		
		if(typeof str == "object"){
			obj = str ;
		}else{
			try {
				obj = JSON.parse(str);
			} catch (e) {
				//console.error(e) ;
				obj = {"data":str} ;
			}
		}
		
		if(typeof obj == "object"){
			let nRow = 0 ;
			let row = {} ;
			// Kalau Dia Object Dan tidak ada Properties data maka object itu sendiri yang kita jadikan data
			if(typeof obj.data == "undefined"){
				obj = {"data":obj} ;
			}
			if(typeof obj.data == "object"){
				nRow = Object.keys(obj.data).length ;
				if(nRow == 0){
					obj = {"data":{}} ;
				}
			} 

			va = {
				data: obj.data,									// Object Data
				dataRows: nRow,									// Jumlah Record
				eof: nRow == 0,									// End Of File
				bof: nRow == 0,									// Begin Of File
				rowNumber: nRow >= 1 ? 1 : 0,		// Posisi Row
				dataType: typeof obj.data,			// Type Data
				src:str,												// source
				status:status,									// status
				get moveNext(){
					this.rowNumber ++ ;
					this.eof = this.rowNumber > this.dataRows ;

					this.rowNumber = Math.min(this.rowNumber,this.dataRows) ;
				},
				get movePrev(){
					this.rowNumber -- ;
					this.bof = this.rowNumber <= 0 ;

					this.rowNumber = Math.max(this.rowNumber,1) ;
				},
				get moveFirst(){this.rowNumber = Math.min(1,this.dataRows);},
				get moveLast(){this.rowNumber = this.dataRows;},
				getValue: function(key,_default=""){
					let _row = this.getRow ;
					if(typeof _row[key] !== "undefined") _default = _row[key] ;
					return _default ;
				},
				get getRow(){
					if(this.dataRows > 0){
						let key = this.getKey ;
						return this.data[key] ;
					}
					return {} ;
				},
				get getKey(){
					if(this.dataRows > 0){
						return Object.keys(this.data)[this.rowNumber-1]
					}
					return "" ;
				},
			}
		}
		return va;
	},
	// Kita akan mencari jumlah parameter Call Back contoh
	// function(a,b){} maka akan menghasilkan result [a,b]
	getCallBackParameters(callBack){
		let STRIP_COMMENTS = /((\/\/.*$)|(\/\*[\s\S]*?\*\/))/mg;
  	let fnStr = callBack.toString().replace(STRIP_COMMENTS, '');
  	let result = fnStr.slice(fnStr.indexOf('(')+1, fnStr.indexOf(')')).split(",");
  	return result;
	},
  // Function Ajax
	Response:{start:0,end:0},		// Untuk menghitung Server Response ajax
  ajax(o,cKey,param,callBack){
		 this.queue = this.queue.then(() => {
			  // Seharunya menggunakan prefix key __
			 	let nonUserActivityKeys = ["KeepAlive", "UpdateTime", "CekDevice"];
				if (typeof idleSession !== 'undefined' && !nonUserActivityKeys.includes(cKey)) {
				  idleSession.resetTimer();
				}
        return new Promise((resolve) => {
				  a.Response.start = new Date() ;
					a.Response.end = 0 ;

					let url = "" ;
					// Kita Membolehkan Parameter ajax dengan object dengan format
					// {"url":url,"key":key,"param":param,"callBack":callBack,"method":urlMethod}
					if(typeof o == "object"){
						if(typeof o.url !== "undefined") url = o.url ;
						if(typeof o.key !== "undefined") cKey = o.key ;
						if(typeof o.param !== "undefined") param = o.param ;
						if(typeof o.callBack !== "undefined") callBack = o.callBack ;
					}else{
						url = o ;
					}

					// Jika mvc maka cKey kita madjikan method kalau parameter pakai tandan () contoh
					// ValidSaving() maka kita akan buang () nya biar tidak error.
					let vaKey = cKey.split("(") ;
					if(svr.IsMVC() && vaKey.length >= 2){
						cKey = vaKey[0] ;
					}else if(!svr.IsMVC() && vaKey.length < 2){
						// Jika Dia Bukan MVC Dan cKey tidak ada () maka kita tambah() karena dia harus call function di ajax.
						cKey = cKey + "()" ;
					}

					if(!url || url == ""){
						if(svr.IsMVC()){
							url = a.mvcURL(cKey) ;
							cKey = "" ;
						}else{
							// Mengambil nama file seumpama mstagama.php maka akan di jadikan mstagama.ajax.php
							url = a.urlByName() ;
						}
					}else if(svr.IsMVC()){
						url += "/" + cKey ;
						cKey = "" ;
					}		

					if(!param) param = "" ;
					if(typeof param == "object" && typeof param.value !== "undefined" && typeof param.name !== "undefined"){
						// Jika Parameter ada Object maka kita ambil parameter name nya = para.value
						let name = param.name ;
						let value = typeof param.value2 !== "undefined" ? param.value2 : param.value ;

						// Jika type input file maka kita gunakan FormData
						if(param.type == "file"){				
							value = param.files[0];
						}
						param = new FormData() ;
						param.append(name,value) ;
					}

					// Kita akan rubah param Jika Formatnya string Query ke FormData, apabila method POST dan param tidak kosong
					let lWithFormData = typeof param == "object" && param instanceof FormData ? true : false ;

					// Jika ajax langsung via http://
					if(url.substring(0,7) == "http:\/\/" || url.substring(0,8) == "https:\/\/"){
						let urlPar = "" ;
						if(cKey !== "") urlPar += "cKey=" + cKey ;			
						if(urlPar !== ""){
							if(url.indexOf("?") >= 0){
								url += "&" ;
							}else{
								url += "?" ;
							}
							 url += urlPar ;
						}
					}else{
						// Ini hanya untuk yang server sama 
						url += "&cKey=" + cKey ;
						url = "ajax.php?__par=" + url ;
					}

					// Jika depan nya ajax.php berarti dia server local dan nonmvc maka __par dll akan kita alihkan ke POST
					let param2 = "" ;
					if(url.substring(0,8) == "ajax.php"){
						let vaURL = url.split("?") ;
						url = vaURL[0] ;
						if(typeof vaURL[1] !== "undefined") param2 = vaURL[1] ;
					}
					// Jika param juga string maka kita gabungkan dengan param2
					if(!lWithFormData) param2 += "&" + param ;

					// Conversi Param ke FormData
					param = a.strQuery2FormData(param2,param) ;
					lWithFormData = true ;

					let page = new XMLHttpRequest() ;
					page.onreadystatechange=function(){
						resolve();
						if(page !== null){
							let cRetval = "" ;
							try {
								if (page.readyState == 4) {
									if (page.status == 200) {
										a.Response.end = new Date() ;
										cRetval = page.responseText ;
										const dataResponse = JSON.parse(cRetval);
										if (dataResponse.code_response) {
											svr._csrfToken = dataResponse.code_response ;
											delete dataResponse.code_response ;
											cRetval = JSON.stringify(dataResponse);
										}
										if(callBack){
											// Kita Lihat Kalau callBack Parameter hanya satu dan varible nya obj maka 
											// Kita hanya kirimkan satu Parameter jawaban
											let par = a.getCallBackParameters(callBack) ;
											let obj = a.str2JSON(cRetval.trim(),page.status) ;
											if(par.length == 1 && par[0] == "obj"){
												callBack(obj) ;
											}else{
												callBack(cRetval.trim(),page.status,obj) ;
											}
										}else{
											eval(cRetval) ;
										}
									}
								}
							}catch(e){
								if(e.message.indexOf('NS_ERROR_NOT_AVAILABLE') < 0){
									a.Response.end = new Date() ;
									cRetval = page.responseText ;
									if(callBack){
										// Kita Lihat Kalau callBack Parameter hanya satu dan varible nya obj maka 
										// Kita hanya kirimkan satu Parameter jawaban
										let par = a.getCallBackParameters(callBack) ;
										let obj = a.str2JSON(cRetval.trim(),page.status) ;
										if(par.length == 1 && par[0] == "obj"){
											callBack(obj) ;
										}else{
											callBack(cRetval.trim(),page.status,obj) ;
										}
									}else{
										eval(cRetval) ;
									}
								}
							}
						}
					} ;

					page.open("POST", url, true) ;
					if(!lWithFormData){
						// Content-Type hanya akan kita definisikan kalau kita menggunakan param dalam bentuk string Query
						// tapi kalau param dalam bentuk object formData tidak usah kita definisikan.
						page.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
					}
					page.setRequestHeader('APP-ID',svr.GetAppID()) ; 			// APP_ID untuk mengakses mvc via ajax untuk akses ke method yang private
					page.setRequestHeader('TOKEN-ID',svr.GetToken()) ; 		// Kira Kirim JWT sebagai Token yang akan di ambil di ajax
					page.setRequestHeader('REQ-ID',"ajax") ;							// Agar Server mengetahui Result yang di harapkan, kalau ajax maka result json.
					//page.setRequestHeader('CSRF-TOKEN',svr.GetCookie('csrfToken')) ;
					//let divcsrfToken = a.getById("divcsrfToken") ;
					//if(divcsrfToken !== null){
						 //page.setRequestHeader('CSRF-TOKEN',divcsrfToken.textContent) ;
					//}
					page.setRequestHeader('CSRF-TOKEN',svr.GetcsrfToken()) ; 

					page.send(param);		
				});
		 });
		
  },
	strQuery2Obj(str){
		// Pindah query ke Object vaPar
		let vaQuery = str.split('&');
		let vaPar = {} ;
		for(let par of vaQuery){
			// Temukan indeks dari '=' pertama
			// Gunakan indeks untuk membagi string menjadi dua bagian
			const index = par.indexOf('=');
			let key = par.substring(0, index) ;
			let value = par.substring(index + 1) ;

			if(key !== ""){
				if(typeof vaPar[key] == "undefined") vaPar[key] = [] ;
				vaPar[key].push(value) ;
			}				
		}

		return vaPar ;
	},
	strQuery2FormData(str,formData=null){
		if(formData == null || typeof formData == "string") formData = new FormData() ;

		// Ubah Dari StringQuery Ke object dulu
		let vaPar = a.strQuery2Obj(str) ;		

		// vaPar Kita Conversi ke param dengan melihat kalau dia ada array atau parameter kembar akan kita beri tanda []
		for(let key in vaPar){
			if(vaPar[key].length == 1){
				formData.append(key,vaPar[key][0]) ;
			}else{
				for(let value of vaPar[key]){
					formData.append(key + "[]",value) ;
				}
			}
		}

		return formData ;
	},
	// Posisi File Aktif kita simpan di div, dan div kita ambil setelah itu kita hapus
	// biar tidak mudah terbaca user.
	cCurrFile:"",
	GetCurrentFile(){
		if(a.cCurrFile == ""){
			let div = a.getById("__currentFile") ;
			if(div !== null){
				a.cCurrFile = div.textContent ;

				a.delObj(div) ;
			}
		}
		return a.cCurrFile ;
	},
  urlByName(){
    let cFile = "" ;
		let str = a.GetCurrentFile() ;
    if(str !== ""){
			cFile = str.substring(0,str.length-4) + ".ajax.php" ;
    }
    return cFile ;
  },
  // Get Form Content
  fContent(elem = null,lformData=null){
    let sXml = "" ;
    let _frm = document.forms[0] ;
    let el = null ;
		let formData = new FormData() ;
		let lSendFile = false ;

    if(elem !== null){
      if(elem.tagName && elem.tagName == 'FORM'){
        _frm = elem ;
      }else{
        el = elem ;
      }
    }
    if (_frm && _frm.tagName == 'FORM'){
      if(el == null) el = _frm.elements ;
      for( var i=0; i < el.length; i++){
        if (!el[i].name)
          continue;
        if (el[i].type && (el[i].type == 'radio' || el[i].type == 'checkbox') && el[i].checked == false)
          continue;
        if (el[i].disabled && el[i].disabled == true)
          continue;

        var name = el[i].name;
        if(name){
          if (sXml != ''){
            sXml += '&';
          }
          if(el[i].type=='select-multiple'){
            for (var j = 0; j < el[i].length; j++){
              if (el[i].options[j].selected == true){
								sXml += name + "=" + el[i].options[j].value + "&" ;
								formData.append(name + '[]', el[i].options[j].value);
              }
            }
          }else{						
						let val = typeof el[i].value2 !== "undefined" ? el[i].value2 : el[i].value ;
						sXml += name + "=" + val;
						
						// Tambahkan Ke formData kalau file kita amil object file nya
						if(el[i].type == "file"){
							lSendFile = true ;
							val = el[i].files[0];
						}
						formData.append(name,val) ;						
          }
        } 
      }
    }

		// Jika lFormData == null maka kita anggap dia auto detect kalau ada send file maka menggunakan formData
		// Tapi jika lformData kita isi false maka kita menggunakan format stringQuery karena ini di pakai untuk laporan dll, yang di submit di openForm.
		// Karena openform dia menggunakan postMessage yang tidak membolehkan parameter object yang memiliki Method.
		let retval = sXml ;
		if(lformData == null && lSendFile){
			retval = formData ;
		}else if(lformData){
			retval = formData ;
		}

    return retval ;
  },
  // Kita Gunakan Kalau kita mau membuat Div Modal maka kita buat Background Terlebih dahulu.
  addBack(cID,parent=null,nOpacity=0.2){
    cID = (cID == null || cID == "") ? "frmBackModal" : cID ;
    let oBack = a.getById(cID,parent) ;
		
		let doc = document.body ;
		if(parent !== null && typeof parent.document !== "undefined" ){
			doc = parent.document.body ;
		} 

		// Kalau Parent null atau parent jenis window maka position fixed selain itu absolute ke parent
		let cPosition = "fixed" ;
		if(parent !== null && typeof parent.document == "undefined"){
			cPosition = "absolute" ;
		}

		if(oBack == null){
      oBack = a.addObj("div",parent,cID,"back_modal") ;
      with(oBack.style){
				position = cPosition ;
        width = doc.scrollWidth-1 ;
        height = doc.scrollHeight-1 ;
				opacity = nOpacity ;
      }

			let button = a.addObj("button",oBack,null,null,"width:1px;height:1px;opacity:0") ;
			fieldfocus(button) ;
			oBack.Button = button ;
      setObjIndex(oBack) ;
    }
    return oBack ;
  },
  // Class Slide Bar Untuk Menyimpan Daftar Windows List kalau ada yang di minimize
	slideBar(){
    var ow = a.gWin ;
    var h = ow.document.body.clientHeight ;

    oDiv = a.getById("__div_slide_bar__",ow) ;
    if(oDiv == null){
      oDiv = a.addObj("div",ow) ;
      oDiv.id = "__div_slide_bar__" ;
      oDiv.className = "slide_bar" ;
      oDiv.onmouseover = function(){a.slideBarItem();} ;
      with(oDiv.style){
        height = h - 5 ;
      }
    }
  },
  slideBarItem(){
    var ow = a.gWin ;
    var cID = "" ;
    var oItem = null ;
    var oDiv = a.getById("__div_slide_bar__",ow) ;
    var h = ow.document.body.clientHeight ;

    if(oDiv !== null){
      with(oDiv.style){
        top = 0 ;
        height = h - 5 ;
      }
      var ot = {} ;
      a.setObjIndex(oDiv) ;
			let winList = gData.Get("winList",{}) ;
      for(var key in winList){
				if(typeof winList[key].title !== "undefined"){				
					el = winList [key] ;
					cID = "__sl_item_" + key + "__" ;
					oItem = a.getById(cID,oDiv) ;
					if(el !== null && oItem == null){
						var ot = a.addObj("div",oDiv) ;
						ot.title = el['title'] ;
						ot.id= cID ;
						ot.className = "slide_bar_item" ;
						((ot)=>{
							ot.onclick = ()=>{
								a.slideBarItemClick(key) ;
							}
						})(ot) ;
						ot.innerHTML = '<div style="margin-right:5px;overflow:hidden">' + el ['title'] + '</div>' ;
					}

					oItem = a.getById(cID,oDiv) ;        
					if(oItem !== null){
						oItem.className = (el ['active']) ? "slide_bar_item" : "slide_bar_item slide_bar_item_blur" ;
					}
				}
      }
    }
  },
  slideBarItemClick(cName){
    var el = gData.Get(["winList",cName],{}) ;
   if(el ['min'] || !el ['active']){
      el ['min'] = false ;
      frm.frmReOpen(cName) ;
    }else{
      el ['min'] = true ;
      frm.min(cName) ;
    }
  },

	// Class Move Object Untuk Semua Form dll.
  x_win:0,y_win:0,x_pos:0,y_pos:0,oMove:null,
  obj_move_start(o,e,callback,callBackWileMove,parent=null){
    if(callback) this.move_callback = callback ;
    if(callBackWileMove) this.callBackWileMove = callBackWileMove ;

		if(parent == null) parent = window ;

		this.oMove = o ;
    this.x_win = o.offsetLeft ;
    this.y_win = o.offsetTop ;
    this.x_pos = parent.document.all ? parent.event.clientX : e.pageX;
    this.y_pos = parent.document.all ? parent.event.clientY : e.pageY;
		o.oldCursor = null ;
		if(o.style.cursor == ""){
			o.oldCursor = o.style.cursor ;
			o.style.cursor = "move" ;
		}
    (function(a,callback,callBackWileMove,parent,o){
      parent.document.onmousemove = function(e){a.obj_move(o,e,callBackWileMove,parent) ;} ;
      parent.document.onmouseup = function(e){a.obj_move_stop(o,e,callback,parent);} ;
    })(this,callback,callBackWileMove,parent,o) ;
  },
  obj_move(o,e,callback,parent){
    var x = parent.document.all ? parent.event.clientX : e.pageX;
    var y = parent.document.all ? parent.event.clientY : e.pageY;
    if (a.oMove !== null) {
      x = Math.max(a.x_win + x - a.x_pos,0) ;
      y = Math.max(a.y_win + y - a.y_pos,0) ;
      a.oMove.style.left = x + 'px';
      a.oMove.style.top = y + 'px';
      if(callback) callback() ;
    }
  },
  obj_move_stop(o,e,callback,parent){
		if(o.oldCursor !== null){
			o.style.cursor = o.oldCursor ;
		}
    parent.document.onmousemove = null ;
    parent.document.onmouseup = null ;
    if(callback) callback() ;
  },
  getCursor(e){   
    var x = (window.Event) ? e.pageX : event.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft);
    var y = (window.Event) ? e.pageY : event.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop);
    return [x,y] ;
  },
  winParent(w){
    return __getParent(w) ;
  },
	isDateValided(date,calFormat="dd-mm-yyyy"){
		let calRE = a.getFormat("dd-mm-yyyy") ;
		lRetval = false ;
		if(calRE.test(date)){
			let va = a.getDateNumbers(date,calFormat) ;
			let d = new Date(va[0],va[1],va[2]);
			if(parseInt(va[0])==d.getFullYear() && parseInt(va[1])==d.getMonth() && parseInt(va[2])==d.getDate()){
				lRetval = true ;
			}
		};
		return lRetval ;
	},
	getDateNumbers(date,calFormat="dd-mm-yyyy"){
		let y, m, d;

		let yIdx = calFormat.search(/yyyy/i);
		let mIdx = calFormat.search(/mm/i);
		let m3Idx = calFormat.search(/mon/i);
		let dIdx = calFormat.search(/dd/i);

		y=date.substring(yIdx,yIdx+4)-0;
		m=date.substring(mIdx,mIdx+2)-1;
		d=date.substring(dIdx,dIdx+2)-0;

		return [y,m,d];
	},
	getFormat(calFormat) {
		let calF = calFormat ;
		calF = calF.replace(/\\/g, '\\\\');
		calF = calF.replace(/\/ /g, '\\\/');
		calF = calF.replace(/\[/g, '\\\[');
		calF = calF.replace(/\]/g, '\\\]');
		calF = calF.replace(/\(/g, '\\\(');
		calF = calF.replace(/\)/g, '\\\)');
		calF = calF.replace(/\{/g, '\\\{');
		calF = calF.replace(/\}/g, '\\\}');
		calF = calF.replace(/\</g, '\\\<');
		calF = calF.replace(/\>/g, '\\\>');
		calF = calF.replace(/\|/g, '\\\|');
		calF = calF.replace(/\*/g, '\\\*');
		calF = calF.replace(/\?/g, '\\\?');
		calF = calF.replace(/\+/g, '\\\+');
		calF = calF.replace(/\^/g, '\\\^');
		calF = calF.replace(/\$/g, '\\\$');

		calF = calF.replace(/dd/i, '\\d\\d');
		calF = calF.replace(/mm/i, '\\d\\d');
		calF = calF.replace(/yyyy/i, '\\d\\d\\d\\d');
		calF = calF.replace(/day/i, '\\w\\w\\w');
		calF = calF.replace(/mon/i, '\\w\\w\\w');

		return new RegExp(calF);
	},
	parentNotCrossOrigin(win=null) {
		if(win == null) win = window ;
		try {
			// Mencoba mengakses parent dari self
			let parent = win.self.parent;

			// Jika berhasil, kita dapat mengakses dokumen parent
			// Namun, ini tidak menjamin bahwa dokumen tersebut bukan cross-origin,
			// karena SOP mungkin mencegah akses ke beberapa properti.

			// Mencoba mendapatkan domain parent (tidak selalu bisa berhasil)
			let parentDomain = parent.document.domain || null;

			// Jika parentDomain tidak dapat diambil, ini mungkin cross-origin.
			return parentDomain !== null;
		} catch (err) {
			// Terjadi kesalahan, mungkin karena SOP melarang akses ke parent.
			return false; // Cross-origin atau terjadi kesalahan
		}
	},
	sec2Time(nSec){
		// Membagi detik menjadi jam, menit, dan detik
		const hours = Math.floor(nSec / 3600);
		const minutes = Math.floor((nSec % 3600) / 60);
		const seconds = (nSec % 60).toFixed(3) ;

		// Mengembalikan string dengan format waktu
		let ret = "" ;
		ret = hours > 0 ? hours + " hr, " : "" ;
		ret += ret !== "" ? minutes + " min, " : "" ;
		ret += seconds + " sec";

		return ret;
	},
	setButtonIcon(button){
		let vaButton = {"cmdAdd":"cmd-add.png","cmdEdit":"cmd-edit.png","cmdDelete":"cmd-delete.png","cmdSave":"cmd-save.png","cmdPreview":"cmd-preview.png","cmdRefresh":"cmd-refresh.png",
										"cmdCari":"cmd-search.png","cmdApply":"cmd-save.png","cmdClose":"cmd-cancel.png","cmdCancel":"cmd-cancel.png","cmdLogin":"cmd-login.png"} ;
		if(button.type.toLowerCase() == "button"){
			let iconFileName = null;
        for(let key in vaButton) {
          if(button.name.startsWith(key)) {
            iconFileName = vaButton[key];
            break;
          }
        }
			if(iconFileName){
				const div = button.parentNode;
				let url = a.OpenIcon(iconFileName) ;
				const img = a.addObj("img",div,null,null,"position: absolute;left: 8px;top: 50%;transform: translateY(-50%);height: 16px;width: 16px;pointer-events: none;") ;
				img.src = url ;
				button.style.textIndent = "18px" ;
			}
		}		
	}
};