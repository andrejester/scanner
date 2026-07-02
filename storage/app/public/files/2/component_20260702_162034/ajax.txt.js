const txt = {
	data:{},lCheckEvent:false,canKeyDown:{},
  init: function(f){		
    if(typeof txt.data[f.id] == "undefined"){
			if(!txt.lCheckEvent){
				txt.lCheckEvent = true ;
				setInterval(txt.eventFound,1000) ;
			}
			
			if(f.type.indexOf("select") >= 0 && typeof f.size !== "undefined" && f.size>1){
				f.style.height = "auto" ;
				f.style.minHeight = "auto" ;
				f.style.maxHeight = "max-content" ;
				f.style.lineHeight = "normal" ;
			}
			let id = f.id.substring(4) ;
			let _c = a.getById("conf-" + id) ;
			let o1 = {required:{},input:{}} ;
			if(_c == null){
				return true ;
			}else{
				let o = a.str2JSON(_c.textContent) ;
				o1 = o.data ;
				o1.required.dataRows = o.dataRows ;

				a.delObj(_c) ;
			}

			txt.data[f.id] = {
				obj:o1.required,
				input:{kp_Number:0,ku_Number:0,kd_Number:0,isPressed:false,buttonClick:false,keyDown:null,keyPress:null,keyUp:null},
				browse:{vaRow:"",value:"",count:0,fieldName:null},
				get keyPressNum(){return txt.data[f.id].input.kp_Number},
				get keyUpNum(){return txt.data[f.id].input.ku_Number},
				get keyDownNum(){return txt.data[f.id].input.kd_Number},
			} ;

			for (const [key, value] of Object.entries(o1.input)) {
				txt.data[f.id].input[key] = value ;
			}

			// Daftar Event
			let e1 = o1.event ;
			let cType = txt.data[f.id].input.type ;
			
			txt.data[f.id].event = {
				onkeydown: ()=>{txt.keyDown(f,event,e1.onKeyDown)},
				onfocus: ()=>{txt.onFocus(f,cType,e1.onFocus)},
				onblur: ()=>{return txt.onBlur(f,cType,e1.onBlur)},
				onkeypress: ()=>{return txt.keyPress(f,event,cType,e1.onKeyPress)},
				onkeyup: ()=>{txt.keyUp(f,event,cType,e1.onKeyUp)},
				onclick: ()=>{txt.fClick(f,cType,e1.onClick)},
				oninput: ()=>{return txt.onChange(f,cType,e1.onChange)},
				onmouseover: ()=>{txt.mOver(f,e1.onMouseOver)},
			}			
			txt.initEvent(f) ;
			
			// Event Mouse Kita Standart
			let ce = ["onMouseMove","onMouseOut","onMouseDown","onMouseUp","onDblClick","onSelect"] ;
      for(var n=0;n<ce.length;n++){
        this.checkEvent(f,ce[n].toLowerCase(),ce[n]) ;
      }

			if(f.type == "radio" || f.type == "checkbox"){
				((f)=>{
					let label = f.parentElement ;
					label.onmouseover = ()=>{
						label.style.cursor = f.disabled ? "not-allowed" : "pointer" ;
					}
				})(f) ;
			}

			// Mendefinisikan properti readOnly dengan setter dan getter kustom
			Object.defineProperty(f, 'readOnly', {
				get: function() {
					return txt.data[f.id].input.readOnly ;
				},
				set: function(newValue) {
					txt.data[f.id].input.readOnly = newValue;
					if(newValue){
						f.classList.add('txtReadOnly');
					}else{
						f.classList.remove('txtReadOnly');
					}					
				}
			});
			f.readOnly = f.readOnly ;

			// Mendefinisikan Properti value2 dengan setter dan getter biar untuk number dan tanggal bisa autoformat
			Object.defineProperty(f,'value2',{
				get: function(){
					let type = txt.data[f.id].obj.type ;
					let value = f.value ;

					if(type == "number"){
						value = String2Number(f.value) ;
					}else if(type == "date"){
						value = Date2String(f.value) ;
					}
					return value ;
				},
				set: function(value){
					let type = txt.data[f.id].obj.type ;

					if(type == "number"){
						value = Number2String(value,txt.data[f.id].obj.num_decimal) ;
					}else if(type == "date"){
						value = String2Date(value) ;
					}	
					f.value = value ;
				}
			}) ;			
    }
  },
	// Kitagunakan untuk mendefinisikan Event yang harus ada di Input kita
	// Dan dia akan membuat ulang kalau sampai di hapus oleh user
	initEvent(f){
		let events = txt.data[f.id].event ;
		for(const key in events){
			if(typeof f[key] !== "function"){
				f[key] = events[key] ;
			}
		}
	},
	// Event ini di jalankan rutin, untuk melihat Field yang aktif apa
	// setelah ketemu field nya akan kita cek apakah event yang wajibkan ada
	// Kalau tidak ada maka kita kita buat ulang, karena dia di hapus oleh user.
	eventFound(){
		let f = document.activeElement ;
		if(f.id !== "" && typeof txt.data[f.id] !== "undefined"){
			txt.initEvent(f) ;
		}
	},
  checkEvent: function(f,cEvent,cFunc){
    cFunc = f.name + "_" + cFunc ;
    if(eval("typeof " + cFunc) == "function"){
			eval("f." + cEvent + " = ()=>{if(f.readOnly) return undefined; " + cFunc + "(f);};") ;
		} 
  },
  keyNum: function(e){
    return a.keyNum(e) ;
  },
  keyDown: function(f,e,cf){
		if(f.readOnly){
			// Kalau Tidak ada Classlist txtReadonly jika keyDown maka akan kita tambahkan Classname itu
			// Hal ini terjadi karena user melakukan edit source melalui inspect
			const classList = f.classList;
			if (!classList.contains("txtReadOnly")) {
				classList.add("txtReadOnly");
			}
			if(e.keyCode !== 13) e.preventDefault();
			return undefined;	// Kalau posisi Readonly kita tidak jalankan eventnya
		} 

		// Kalau autocomplete tidak off maka kita off kan
		if(f.autocomplete !== "off") f.autocomplete = "off";

		// Kalau ada pesan Tooltips maka kita Hapus
		txt.hideTip(f) ;
		
    cf = decodeURIComponent(cf) ;
    let ludf = cf !== "" ;
		let keynum = this.keyNum(e) ;
    if(!ludf) cf = f.name + "_onKeyDown" ;
    txt.data[f.id].input.kd_Number = keynum ;
    txt.data[f.id].input.kp_Number = 0 ;
    txt.data[f.id].input.ku_Number = 0 ;
		txt.data[f.id].input.oldValue = f.value ;

    if(txt.data[f.id].input.keyDown == null) txt.data[f.id].input.keyDown = eval("typeof " + cf) == 'function' ;
    if(ludf) eval(cf) ;

    else if(txt.data[f.id].input.keyDown) eval(cf + "(f," + this.keyNum(e) + ");") ;
    txt.data[f.id].input.isPressed = true ;
		
		// Kita akan cari karakter untuk enter, panah, tab dll untuk pindah field
		txt.keyEnter(f,keynum) ;
  },
  lastKeyPress: function(f){
    return txt.data[f.id].input.kp_Number ;
  },
  lastKeyDown: function(f){
    return txt.data[f.id].input.kd_Number ;
  },
  lastKeyUp: function(f){
    return txt.data[f.id].input.ku_Number ;
  },
  keyUp: function(f,e,cType,cf){
		if(f.readOnly) return undefined;	// Kalau posisi Readonly kita tidak jalankan eventnya

		// Kalau Keyup maka canKeyDown kita true 
		txt.canKeyDown.canClick = true ;
		
    cf = decodeURIComponent(cf) ;
    var ludf = cf !== "" ;
    var keynum = this.keyNum(e) ;
    txt.data[f.id].input.ku_Number = keynum ;
    if(!ludf) cf = f.name + "_onKeyUp" ;
    if(txt.data[f.id].input.keyUp == null) txt.data[f.id].input.keyUp = eval("typeof " + cf) == 'function' ;
    if(ludf) eval(cf) ;
    else if(txt.data[f.id].input.keyUp) eval(cf + "(f," + keynum + ")") ;
    txt.data[f.id].input.isPressed = false ;
  },
  keyPress: function(f,e,cType,cf){
		if(f.readOnly) return undefined;	// Kalau posisi Readonly kita tidak jalankan eventnya

    cf = decodeURIComponent(cf) ;
    var ludf = cf !== "" ;
    var keynum = this.keyNum(e) ;
    txt.data[f.id].input.kp_Number = keynum ;
		if(!ludf) cf = f.name + "_onKeyPress" ;    
    
    if(txt.data[f.id].input.keyPress == null){
			txt.data[f.id].input.keyPress = eval("typeof " + cf) == 'function' ;
		} 
    if(ludf){
			eval(cf) ;
		}else if(txt.data[f.id].input.keyPress){
			eval(cf + "(f," + keynum + ")") ;
		}
    if(cType == "number"){
      if(!((e.shiftKey && keynum == 0) || e.ctrlKey)){
        if (!(keynum == 0 || keynum == 45 || keynum == 46 || keynum == 8 || keynum == 37 ||keynum == 39 || (keynum >= 48 && keynum <= 57))) {
          e.preventDefault();
        }
      }
    }

    return true ;
  },
  bmd: function(cName){
    var f = a.getById("txt-" + cName) ;
    this.init(f);
    txt.data[f.id].input.buttonClick = true ;
  },
  bClick: function(cName,cType,cf){
    cf = decodeURIComponent(cf) ;
    let ludf = cf !== "" ;
    let f = a.getById("txt-" + cName) ;

		// Kalau Button di click kita kasih jeda 500ms kelau kurang dari itu kita anggak double click
		// dan diabaikan
		if(!txt.canClick(f,1000)) return false ;

    if(!ludf) cf = f.name + "_onButtonClick" ;
    if(!f.readOnly && !f.disabled){    
      if(cType == "date"){
				cal.show(f) ;
      }else{
        if(ludf){
					if(cf.substring(0,5).toLowerCase() == "ajax:"){
						this.onAjax(cf.substring(5)) ;
					}else{
						eval(cf) ;
					}
				}else if(eval("typeof " + cf) == 'function'){
					eval(cf + "(f)") ;
				}
      }
    }
    return false ;
  },
  onFocus: function(f,cType,cf){
		if(f.readOnly) return undefined;	// Kalau posisi Readonly kita tidak jalankan eventnya

    cf = decodeURIComponent(cf) ;
    var ludf = cf !== "" ;    
    if(!ludf) cf = f.name + "_onFocus" ;
    this.init(f) ;
    txt.data[f.id].input.buttonClick = false ;
    txt.data[f.id].input.kd_Number = 0 ;
    txt.data[f.id].input.kp_Number = 0 ;
    txt.data[f.id].input.ku_Number = 0 ;
		txt.data[f.id].input.oldValue = f.value ;

    if(ludf) eval(cf) ;
    else if(eval("typeof " + cf) == 'function') eval(cf + "(f)") ;
  },
	focus(f,cType=null){
		if(typeof tab !== "undefined" && typeof f.sisTab !== "undefined" && f.sisTab !== tab.currTab()) tab.click(f.sisTab) ;
		f.focus() ;
		cType = cType == null ? f.type : cType ;
		if(typeof txt.data[f.id] !== "undefined" && typeof txt.data[f.id].input !== "undefined") cType = txt.data[f.id].input.type ;

		if(cType == "text" || cType == "number" || cType == "date"){
			let nEnd = f.value.length ;
			let nStart = 0 ;
			if(cType == "date"){
				nEnd = 0 ;
			}else if(cType == "number"){
				let dot = f.value.lastIndexOf(".");
				if(dot >= 0){
					nStart = dot ;
					nEnd = dot ;
				}else{
					nStart = nEnd ;
				}
			}

			f.setSelectionRange(nStart, nEnd);
		}
	},
  mOver: function(f,cf){
		if(f.readOnly) return undefined;	// Kalau posisi Readonly kita tidak jalankan eventnya

    cf = decodeURIComponent(cf) ;
    var ludf = cf !== "" ;
    if(!ludf) cf = f.name + "_onMouseOver" ;
    this.init(f) ;

    if(ludf) eval(cf) ;
    else if(eval("typeof " + cf) == 'function') eval(cf + "(f)") ;
  },
  onAjax: function(cFunc){
    ajax('',cFunc,GetFormContent()) ;
  },
	// Event ini berfungsi untuk mengantisipasi double click dianggap sebagai click 2x.
	canClick(f,nTime=500,callBack=null){
		// Kalau Button di click kita kasih jeda 500ms kelau kurang dari itu kita anggak double click
		// dan diabaikan
		if(typeof f.canClick == "undefined") f.canClick = true ;
		if(f.canClick){
			f.canClick = false ;
			setTimeout((f)=>{f.canClick = true},nTime,f) ;
			if(callBack) callBack() ;
			return true ;
		}
		return false ;
	},
	// Event onClick
	fClick: function(f,cType,cf){
		if(f.readOnly) return undefined;	// Kalau posisi Readonly kita tidak jalankan eventnya

		// Kalau Button di click kita kasih jeda 1000ms kalau kurang dari itu kita anggak double click
		// dan diabaikan
		if(!txt.canClick(f,1000)) return false ;
		
		// Jika Jenis Button Maka Padasaat di Click kita simpan kapan di click
		if(f.type == "button") f.LastClick = new Date() ;

		cf = decodeURIComponent(cf) ;
		cf = cf.replace(/&quot;/g, '"');

		// Jika User Mendefinisikan Event maka kita evel event nya
		if(cf !== ""){
			if(cf.substring(0,5).toLowerCase() == "ajax:"){
				this.onAjax(cf.substring(5)) ;
			}else{
				eval(cf) ;
			}
		}else{
			cf = f.name + "_onClick" ;
			if(eval("typeof " + cf) == "function"){
				l = eval(cf+"(f);") ;
			}
		}
	},
  onBlur: function(f,cType,cf){
		if(f.readOnly) return undefined;	// Kalau posisi Readonly kita tidak jalankan eventnya

    cf = decodeURIComponent(cf) ;
    var ludf = cf !== "" ;
    if(!ludf) cf = f.name + "_onBlur" ;

    var l = true ;
    if(!txt.data[f.id].input.buttonClick){
      if(cType == "date"){
        if(!a.isDateValided(f.value)){
					txt.showTip(f,"Tanggal Tidak Valid ......");
          fieldfocus(f) ;
          return false ;
        }
      }else if(cType == "number"){
        if(!CheckNumber(f)){
          return false ;
        }
      }

      if(ludf){
        if(cf.substring(0,5).toLowerCase() == "ajax:"){
          this.onAjax(cf.substring(5)) ;
        }else{
          eval(cf) ;
        }
      }else if(eval("typeof " + cf) == "function"){
				f.event = "onBlur" ;
        l = eval(cf+"(f);") ;
      }
    }
		txt.fieldValidated(f) ;
		if(typeof tab !== "undefined") tab.lastFieldFocus(f) ;				// Terakhir Field Focus kita simpan di tab biar kalau di click bisa kita kembalikan
    return l ;
  },
	onChange: function(f,cType,cf){
		cf = decodeURIComponent(cf) ;

		// Untuk Number / Date Kita akan format yang di entry User
		if(cType == "number"){
			txt.numFormat(f) ;
		} else if(cType == "date") {
			txt.dateFormat(f) ;
		} 
		
		// Jika User Mendefinisikan Event maka kita evel event nya
		if(cf !== ""){
			eval(cf) ;
		}else{
			cf = f.name + "_onChange" ;
			if(eval("typeof " + cf) == "function"){
				l = eval(cf+"(f);") ;
			}
		}
		return true ;
  },
	dateFormat(f) {
		let inputValue = f.value.replace(/[^0-9-]/g, ''); // Remove non-numeric characters
		let nCursor = f.selectionStart ;
		if(inputValue.length <= 10) nCursor ++ ;

		let va = inputValue.split("-") ;
		if(typeof va[0] !== "undefined"){
			va[0] = va[0].substring(0,2) ;
			if(va[0].length == 2 && nCursor == 2){
				va[0] = "0" + Math.max(1,Math.min(31,parseInt(va[0]))).toString() ;
				va[0] = va[0].slice(-2);
			} 
		} 
		if(typeof va[1] !== "undefined"){
			va[1] = va[1].substring(0,2) ;
			if(va[1].length == 2 && nCursor == 5){
				va[1] = "0" + Math.max(1,Math.min(12,parseInt(va[1]))).toString() ;
				va[1] = va[1].slice(-2);
			} 
		}
		if(typeof va[2] !== "undefined"){
			va[2] = va[2].substring(0,4) ;
		}
		inputValue = va.join("") ;

		if (inputValue.length >= 2) {
			inputValue = inputValue.replace(/^(\d{2})/, '$1-');
		}
		if (inputValue.length >= 5) {
			inputValue = inputValue.replace(/-(\d{2})/, '-$1-');
		}
		if (inputValue.length >= 10) {
			inputValue = inputValue.replace(/-(\d{4})/, '-$1');
		}

		// Update the input value with the formatted result
		if(inputValue.substring(nCursor,nCursor+1) == "-") nCursor ++ ;

		f.value = inputValue.substring(0,10)
		f.setSelectionRange(nCursor, nCursor);
		
		if(!a.isDateValided(f.value)){
			txt.showTip(f,"Tanggal Tidak Valid, format dd-mm-yyyy");
		}
	},
	numFormat(f,nDecimal=null,nKeyDown=null,nKeyPress=null) {
		let input = f.value.replace(/[^0-9.-]/g, ''); // Remove non-numeric characters
		let nSelStart = f.selectionStart ;
		let nCharRight = Math.max(0,f.value.length - nSelStart) ;

		nKeyDown = nKeyDown == null ? txt.lastKeyDown(f) : nKeyDown ;
		nKeyPress = nKeyPress == null ? txt.lastKeyPress(f) : nKeyPress ;		

		input = input.replace("..",".") ;
		input = input.replace(/^0+/, ''); // Remove leading zeros

		// Jika Menekan Karakter (-), jika karakter positif maka jadi negatif, jika negatif maka jadi positif
		if(nKeyPress == 45){
			const vaMin = input.split("");
			let cMin = "" ;
			input = "" ;
			for(const _c of vaMin){
				if(_c == "-"){
					cMin = cMin == "" ? "-" : "" ;
				}else{
					input += _c ;
				}
			}
			input = cMin + input ;
		}

		// Insert a comma at the third-to-last position
		let vaInput = input.split(".") ;
		input = vaInput[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,') ;
		if(typeof vaInput[1] !== "undefined"){				
			// Kalau Posisi Kursor Setelah Titik dan User Memasukkan Angka
			// Maka kita akan potong angka di belakang koma sesuai dengan panjang decimal di belang koma
			nDecimal = nDecimal == null ? txt.data[f.id].obj.num_decimal : nDecimal ;
			if(nDecimal > 0 && vaInput[1].length > nDecimal){
				nCharRight = Math.max(0,nCharRight-1) ;
				vaInput[1] = vaInput[1].substr(0,nDecimal) ;
			}

			if(input == "") input = "0" ;
			if(nDecimal > 0) input = input + "." + vaInput[1] ;
		} 
		// Jika Tekan titik maka Cursor kita arahkan ke setelah titik
		if(nKeyPress == 46 && vaInput[1] !== "undefined"){
			nCharRight = vaInput[1].length ;
			if(nDecimal == 0) txt.showTip(f,"Input hanya boleh berisi angka tanpa desimal") ;
		}

		// Kita akan tempatkan Carakter posisi yang sama dari kanan
		nSelStart = Math.max(0,input.length - nCharRight) ;

		// Update the input value with the formatted result
		f.value = input;	

		f.setSelectionRange(nSelStart, nSelStart);
	},
	globalPos(vaPos=null){
		if(vaPos == null) vaPos = {top:0,left:0,width:0,height:0}
		let scrTop = document.body.scrollTop ;
		let scrLeft = document.body.scrollLeft ;
		let winWidth = window.innerWidth ;
		let winHeight = window.innerHeight ;
		let va = {
			scr:{top:scrTop,
					 left:scrLeft,
					 right:scrLeft+winWidth,
					 bottom:scrTop+winHeight,
					 width:winWidth,
					 height:winHeight,
					},			
			top:vaPos.top + frm.funcPos.top + scrTop,
			left:vaPos.left + frm.funcPos.left + scrLeft,
			width:vaPos.width,
			height:vaPos.height,
		}
		va.bottom = va.top + vaPos.height ;
		va.right = va.left + vaPos.width ;
	
		/*let divTest = a.addObj("div",null,null,null,"position:absolute;background:#000") ;
		with(divTest.style){
			top = va.top ;
			left = va.left ;
			width = va.width ;
			height = va.height ;
		}
		setObjIndex(divTest) ;*/
		
		return va ;
	},
	showTip(field,cTitle) {
		txt.hideTip() ;		// Tutup Tooltip yang lama
		setTimeout(()=>{
			document.addEventListener("keydown",txt.hideTip) ;
			document.addEventListener("mousedown",txt.hideTip);
		},200) ;

		let vaPos = field.getBoundingClientRect() ;
		frm.callFunc("txt._showTip",[cTitle,vaPos],"mainFrame") ;
	},
	_showTip(cTitle,vaPos){
		document.addEventListener("mousedown",txt.hideTip);
		let div = a.addObj("div",null,"error-tooltip","error-tooltip",null,cTitle); // ❗❕
		va = txt.globalPos(vaPos) ;

		let nTop = va.bottom + 10 ;
		let nLeft = va.left - 5 ;
		let nRight = nLeft + div.offsetWidth ;
		let nBottom = nTop + div.offsetHeight ;
		
		// Jika Batas Kanan Lebih Besar Dari Screen Kita Geser Kekiri
		if(nRight > va.scr.right){
			let nMove = nRight - va.scr.right + 15
			nLeft = nLeft - nMove ;
			
			// Kita Atur Posisi segitiga panah ke field kita geser
			let nTip = va.left - nLeft + 10 ;
			div.style.setProperty('--tooltip-after-left', nTip + 'px');
		}
		
		// Jika Batas Bawah Melebihi Screen Maka kita pindah Ke atas.
		if(nBottom > va.scr.bottom){
			nTop = va.top - div.offsetHeight - 10 ;
			
			// Di Tooltip ada segitiga untuk panah ke field kita akan geser kalau tooltip digeser 
			div.style.setProperty('--tooltip-after-top', (div.offsetHeight-8) + 'px');
			div.style.setProperty('--tooltip-after-rotate', '135deg');
		}
		div.style.top = nTop + "px" ; // document.body.scrollTop + vaPos.bottom + frm.funcPos.top + 10 + "px";
		div.style.left = nLeft + "px" ; //document.body.scrollLeft + vaPos.left + frm.funcPos.left - 5 + "px";

		setObjIndex(div) ;
	},
	hideTip() {
		document.removeEventListener("mousedown",txt.hideTip);
		document.removeEventListener("keydown",txt.hideTip) ;
		
		if(window.name !== "mainFrame") return frm.callFunc("txt.hideTip",null,"mainFrame") ;


		const divs = document.querySelectorAll("div#error-tooltip");
		for (const div of divs) {
			a.delObj(div) ;
		}
	},
	// Function Untuk Setting Awal Component Khusus nya component yang type required.
	initRequired: function(f){			
		// Kita Mendefinisikan Object yang akan kita tempelkan ke obj input juga 
		if(typeof f.Browse == "undefined") f.Browse = txt.newBrowse ;

		let id = f.id.substring(4) ;
		txt.inputError(f,true) ;

		if(typeof txt.data[f.id] == "undefined") return false ;
		if(typeof f.Required == "undefined"){
			txt.init(f) ;
			f.Required = {
				// Status
				get status(){return txt.data[f.id].obj.required ;},
				set status(par){this.setProper("required",par) ;},
				// Char
				get char(){return txt.data[f.id].obj.char ;},
				set char(par){this.setProper("char",par) ;},
				// Max
				get max(){return txt.data[f.id].obj.max ;},
				set max(par){this.setProper("max",par) ;},
				// Min
				get min(){return txt.data[f.id].obj.min ;},
				set min(par){this.setProper("min",par) ;},
				// numDecimal
				get numDecimal(){return txt.data[f.id].obj.num_decimal ;},
				set numDecimal(par){this.setProper("num_decimal",par) ;},
				// dateFormat
				get dateFormat(){return txt.data[f.id].obj.date_format ;},
				set dateFormat(par){this.setProper("date_format",par) ;},
				// Pattern
				get pattern(){return txt.data[f.id].obj.pattern ;},
				set pattern(par){this.setProper("pattern",par) ;},
				// Title
				get title(){return txt.data[f.id].obj.title ;},
				set title(par){this.setProper("title",par) ;},
				reload: function(){
					txt.initRequired(f) ;
				},
				setProper: function(key,newValue){
					if(eval("txt.data[f.id].obj." + key + " !== newValue")){
						 eval("txt.data[f.id].obj." + key + " = newValue") ;
						 txt.initRequired(f) ;
					}
				}
			}
		} 
		let obj = txt.data[f.id].obj ;
		if(obj.dataRows > 0){
			let cTitle = "" ;
			if(obj.required){
				cTitle = obj.title ;
				if(cTitle == ""){
					cTitle = "Data harus diisi ..." ;
					if(obj.min !== "" || obj.max !== ""){
						if(obj.type == "number"){
							let nDec = Math.max(obj.num_decimal,0) ;
							let _min = String2Number(obj.min,nDec) ;
							let _max = String2Number(obj.max,nDec) ;
							if(_min == 0 && _max == 0){
								cTitle = "Nilai Minimal >= [" + Number2String(_min,nDec) + "]" ;
							}else	if(_min <= _max){
								cTitle = "Range data : [" + Number2String(_min,nDec) + "] s/d [" + Number2String(_max,nDec) + "]" ;
							}else{
								cTitle = "Nilai Minimal >= [" + Number2String(_min,nDec) + "]" ;
							}
						}else if(obj.type == "text"){
							cTitle = "Panjang Data : [" + Number2String(obj.min,0) + " Karakter] s/d [" + Number2String(obj.max,0) + " Karakter]" ;
							if(obj.min == obj.max){
								cTitle = "Panjang Data Harus " + Number2String(obj.min,0) + " Karakter" ;
							}
						}
					}
				}
			}
			f.title = cTitle ;
			
			let req = txt.data[f.id].divRequired ;
			if(!obj.required || obj.char == ""){
				if(typeof req !== "undefined") req.style.display = "none" ;
			}else{
				// Tanda Bintang di kanan akan kita buang kalau not required atau char kosong
				if(typeof txt.data[f.id].divRequired == "undefined"){
					let div = a.getById("container-"+id) ;
					txt.data[f.id].divRequired = a.addObj("div",div,"required-" + id,"txtRequired",null,obj.char) ;
					req = txt.data[f.id].divRequired ;
				}
				req.style.display = "" ;
				req.title = cTitle ;
			}			
		}
	},
	fieldValidated: function(f){
		/*
		obj field list
		required: false
		char: '*'
		min: ''
		max: ''
		type: 'Button'
		num_decimal: -1
		date_format: "dd-mm-yyyy"
		*/
		if(typeof f.Required == "undefined") txt.initRequired(f) ;

		let obj = txt.data[f.id].obj ;
		let lRetval = true ;		
		if(obj.dataRows > 0){
			if(obj.type == "number" && obj.num_decimal >= 0) f.value = Number2String(f.value,obj.num_decimal) ;

			if(obj.required){
				// Jika Decimal Lebih dari dua kita format angkanya
				if(obj.type == "number"){
					let _min = String2Number(obj.min) || 0;
					let _max = String2Number(obj.max) || 0 ;
					let _n = String2Number(f.value) ;
					if(_min == 0 && _max == 0){	// Kalau Min=0 dan Max = 0 maka tidak di lihat nilainya yang penting angka
						lRetval = true ;
					}else if(_min > _max){			// Jika Minimal diisi lebih besar dari Maximal maka kita hanya pakai minimal
						lRetval = _n >= _min ;
					}else{											// Selain itu Kita akan cek range antara dua min dan max
						lRetval = _n >= _min && _n <= _max ;
					}
				}else if(obj.type == "text" || obj.type == "password"){
					if(obj.pattern !== ""){
						lRetval = f.value.match(obj.pattern) ;
					}

					// Jika Pattern valid atau Pattern Kosong kita lakukan Pengecekan berikut, tapi kalau sudah error di pattern lainnya tidak usah di cek
					if(lRetval){
						obj.min = parseInt(obj.min) || 0 ;
						obj.max = parseInt(obj.max) || 0 ;
						if(obj.min == 0 && obj.max == 0){		// Jika min=0 dan max=0 maka yang penting ada isinya
							lRetval = f.value !== "" ;
						}else if(obj.min > obj.max){				// Jika min > max maka kita hanya perhitungkan min nya
							lRetval = f.value.trim().length >= obj.min ;
						}else{															// Jika di isi dua-dua nya maka kita anggap panjang harus di atara keduanya
							lRetval = f.value.trim().length >= obj.min && f.value.trim().length <= obj.max ;
						}
					}
				}else if(obj.type == "date"){
					lRetval = true ;					
					// Data Data Tidak Valid Langsung kita result false
					if(!a.isDateValided(f.value)){
          	lRetval = false ;
        	}else{
						let nDate = new Date(Date2String(f.value)).getTime() ;
						if(obj.min !== ""){
							let _min = new Date(Date2String(obj.min)).getTime() ;
							lRetval = nDate >= _min ;
						}
						if(lRetval && obj.max !== ""){
							let _max = new Date(Date2String(obj.max)).getTime() ;
							lRetval = nDate <= _max ;
						}
					}					
				}else{
					lRetval = f.value !== "" ;
				}

				txt.inputError(f,lRetval) ;
			}
		}
		return lRetval ;
	},
	inputError(f,lPar){
		let id = f.id.substring(4) ;
		if(lPar){
			f.classList.remove("input-error") ;
		}else{
			if (!f.classList.contains("input-error")) {
				f.classList.add("input-error");
			}
		}
	},
  keyEnter:function(f,nKeyCode){
		if(nKeyCode == 9){
			// Tab akan kita anggap panak ke bawah
			// Kalau tekan shift tab di anggap panah atas.
			nKeyCode = event.shiftKey ? 38 : 40 ;
		}

    if ((nKeyCode == 13 && f.type !== "button") || ((nKeyCode == 38 || nKeyCode == 40) && f.type !== "select-one" && f.type !== "select-multiple")){
			// Kita batasi boleh lanjut per 150ms, dan kita cek di form bukan di field.
			if(!txt.canClick(txt.canKeyDown,150)){
				event.preventDefault();
				return false ;
			}

      let x = 0 ;
      let i = f.form.length ;
      let n ;
      let lFocus = false ;
      while(x<i){
        if(nKeyCode == 38){
          n = i - x - 1 ;
        }else{
          n = x ;
        }
				
        if (lFocus && !f.form[n].disabled && !f.form[n].readOnly && f.form[n].type.toLowerCase() !== "hidden" && 
						f.form[n].name !== f.name && txt.isElementVisible(f.form[n])){
          lFocus = false ;
					
					// Jika Menemukan Jenis Variable Radio Button maka akan kita cari yang posisi di Checked itu yang dijadikan standart Focus
          if(f.form[n].type.toLowerCase() == "radio"){
            let rd = n ;
            let nFound = 0 ;
            while(nFound == 0 && f.form[n].type == "radio" && n < i && f.form[n].name == f.form[rd].name){
              if(f.form[n].checked){
                nFound = n ;
                n = i ;
              }

							// Kalau Panak Atas maka kita kurangi
							if(nKeyCode == 38){
								n -- ;
							}else{
								n ++ ;
							}              
            }
            n = (nFound > 0) ? nFound : rd ;
          }

					if(typeof tab !== "undefined" && typeof f.form[n].sisTab !== "undefined" && f.form[n].sisTab !== tab.currTab()) tab.click(f.form[n].sisTab) ;
          f.form[n].focus() ;
					let cType = ",text,number," ;					
          if (cType.indexOf("," + f.form[n].type.toLowerCase() + ",") >= 0) fieldfocus(f.form[n]) ;

          x = i ;
        }
				// Jika Field Ketemu
        if (f.form[n] == f) lFocus = true ;
        x ++ ;
      }
			event.preventDefault();
    }
  },
	/*
	ini untuk mendeteksi apakah element terlihat di form ini untuk mendeteksi kalau ada object induk yang visible
	contoh
		<input field1>
		<div sytle="display:none">
		  <input field2>
		</div>
		<input field3>
		
	pada kasus di atas dia harusnya dari field1 langsung lompat ke field3, karena field2 tidak terlihat.
	*/
	isElementVisible(element) {
		let lRetval = element.offsetParent !== null ;		// Kalau Induknya ada yang hidden maka offsetparrent akan null
		if(!lRetval){
			// Kalau posisi induk dari Input itu Hidden kita akan cek lagi 
			// Apakah hidden karen tab atau karena memang ada compononent induk hidden
			// caranya kita bandingkan apakah ada element sisTab kalau ada ( sisTab akan muncul kalau component berada di dalam sistab)
			// Bandingkan dengan tab.currTab() kalau sama berarti ada komponent induk yang hidden
			if(typeof tab !== "undefined" && typeof element.sisTab !== "undefined" && element.sisTab !== tab.currTab()){
				lRetval = true ;
				tab.click(element.sisTab) ;
			}
		}
		return lRetval ;
	},
	newBrowse: function(method,parameter,callBack,lSkipEmpty=false,url=""){
		txtBtn.Browse(method,this,callBack,parameter,true,lSkipEmpty,url) ;
	},
};