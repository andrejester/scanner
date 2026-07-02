var cRetval = "" ;
var loadedobjects = "" ;
function ajax(url,cKey,cParameter){a.ajax(url,cKey,cParameter)}
function URL_Ajax(){return a.urlByName() ;}
function __onKeyPress(e){return txt.keyNum(e)}
function validate(field,e){txt.keyEnter(field,txt.keyNum(e))}
function GetFormContent(par=null){return a.fContent(par) ;}

function loadpageornot(url,containnerid){
	var div = a.getById(containnerid) ;
	if(div !== null){
		if(div.innerHTML == ""){
			a.ajax(url,"","",function(cData,nStatus){div.innerHTML = cData}) ;
		}else{
			div.innerHTML = "" ;
		}
	}
	return false ;
}

function loadpage(url,containerid,param){
	a.ajax(url,"",param,function(cData,cStatus){
		var o = a.getById(containerid) ;
		if(o !== null) o.innerHTML = cData ;
	}) ;
}

function dragStart(oRow,event, id) {
	var o = a.getById(id) ;
	if(o !== null)a.obj_move_start(o,event) ;
}

function SetOpt(opt,cValue){  
	for(n=0;n<opt.length;n++){
		if(opt[n].value == cValue){
			opt[n].checked = true ;
		}
	}
}

function GetOpt(opt){
	var cRetval = "" ;
	for(n=0;n<opt.length;n++){
		if(opt[n].checked){
			cRetval = opt[n].value ;
		}
	}  
	return cRetval ;
}

function readCookie(name){
	var cookieValue = "";
	var search = name + "=";
	if(document.cookie.length > 0){ 
		offset = document.cookie.indexOf(search);
		if (offset != -1){ 
			offset += search.length;
			end = document.cookie.indexOf(";", offset);
			if (end == -1) end = document.cookie.length;
			cookieValue = unescape(document.cookie.substring(offset, end)) ;
		}
	}
	return cookieValue;
}

function writeCookie(name, value, hours){
	var expire = "";
	if(hours == null){
		expire = new Date((new Date()).getTime() + hours * 3600000);
		expire = "; expires=" + expire.toGMTString();
	}
	document.cookie = name + "=" + escape(value) + expire + "; Path=/"; //path root agar one for all
}

function Number2String(nNumber,nDec=2){
	if(typeof nNumber !== "string" && typeof nNumber !== "number") nNumber = "" ;
	let cMin = nNumber.toString().trim().substring(0,1) ;
	cMin = cMin == "-" ? "-" : "" ;

	nNumber = nNumber.toString().replace(/[^0-9.]/g, ''); ;
	nNumber = parseFloat(nNumber) || 0 ;
	nNumber = nNumber.toFixed(nDec) ;
	
	let vaNumber = nNumber.toString().split(".") ;
	nNumber = vaNumber[0].replace(/(\d)(?=(\d{3})+$)/g, '$1,') ;
	if(typeof vaNumber[1] !== "undefined" && vaNumber[1] !== "") nNumber = nNumber + "." + vaNumber[1] ;

	return cMin + nNumber ;
}

function String2Number(cString){
	if(cString == null) cString = "" ;
	var i;
	var cRetval = "";
	var ValidChars = "-0123456789." ;
	var cChar = "" ;
	cString = cString.toString() ;
	for(i=0;i<cString.length;i++){
		cChar = cString.charAt(i) ;    
		if (ValidChars.indexOf(cChar) >= 0){
			cRetval = cRetval + cChar ;
		}
	}
	cRetval = parseFloat(cRetval) || 0 ;
	return cRetval;
}

function fieldfocus(field,cType=null){
	txt.focus(field,cType) ;
}

var fieldX=0, fieldY=0;
function fieldPos(field,w=null){
	var x = 0, y = 0;
	var elm = field ;
	var el = elm ;
	var n = 0 ;
	if(w == null) w = window ;

	if(window.name == "subModule") y = parseInt(field.offsetHeight) ;
	while(el !== null && n++ < 50){
		if(typeof el.tagName == "string" && el.tagName.toLowerCase() == "div"){
			if(typeof el.scrollTop == "number") y -= parseInt(el.scrollTop) ;
		}
		el = el.parentNode ;
	}
	x += elm.offsetLeft + 1;
	y += elm.offsetTop + 1 ;

	elm = elm.offsetParent;
	lLoop = true ;
	while(elm != null && lLoop){
		x = parseInt(x) + parseInt(elm.offsetLeft);
		y = parseInt(y) + parseInt(elm.offsetTop);
		elm = elm.offsetParent;
		if(elm == null && w.name !== ""){
			var cWinName = w.name ;
			p = __getParent(w) ;
			lLoop = p [1] ;
			if(p [1]){
				w = p [0] ;
				elm = a.getById(cWinName,w) ;

				// Lihat Kalau ada Header maka kita ambil Tinggi header nya
				if(w.name !== "mainFrame"){
					var oHead = a.getById(cWinName + "-header",w) ;
					if(oHead !== null){
						x = parseInt(x) + 3 ;
						y = parseInt(y) + parseInt(oHead.offsetHeight) + 1;
					}
				}
			}
		}
	}
	fieldX = x ;
	fieldY = y ;
	return {0:fieldX,1:fieldY,2:{"left":x,"top":y},"left":x,"top":y} ;
}

function Browse(cSQL,cField){
	var field = cField ;
	if(typeof cField == "string") eval("field = document.form1." + cField) ;
	a.Browse(cSQL,field) ;
}

function setObjIndex(_obj){
	return a.setObjIndex(_obj) ;
}

function OpenForm(URL,cFormName,cTitle,nWidth,nHeight,cBackColor,lShowModal,cFormScroll,lHideToolBox,cFrameName,lReport){
	frm.open(URL,cFormName,cTitle,nWidth,nHeight,cBackColor,lShowModal,cFormScroll,lHideToolBox,cFrameName,lReport) ;
}

function CloseForm(cFormName){
	frm.close(cFormName) ;
}

function OpenReport(URL,lPrintDialog){
	rpt.open(URL,lPrintDialog) ;
}

function _Ajax_Event(cFunction){
	ajax('',cFunction,GetFormContent()) ;
}

function IsNumeric(sText,lComma){
	var ValidChars = "0123456789.-";
	var cChar = 0 ;
	var IsNumber = true ;
	if(lComma) ValidChars += "," ;
	for (i = 0; i < sText.length && IsNumber == true; i++){ 
		cChar = sText.charAt(i) ; 
		if (ValidChars.indexOf(cChar) == -1){
			IsNumber = false ;
		}
	}
	return IsNumber ;
}

function CheckNumber(field){
	if(!IsNumeric(field.value,true)){
		alert("Invalid Number ....!") ;
		field.value = 0 ;
		fieldfocus(field) ;
		return false ;
	}
	return true ;
}

function Date2String(dTgl){
	cRetval = dTgl.substring(0,10) ;
	va = dTgl.split("-") ;
	// Jika Array 1 Bukan Tahun maka akan berisi 2 Digit
	if(va [0].length == 2 && va.length >= 3){
		cRetval = va [2] + "-" + va [1] + "-" + va[0] ;
	}
	return cRetval ;
}

function String2Date(cString){
	if(cString == null) cString = "" ;
	cRetval = cString.substring(0,10) ;
	va = cString.split("-") ;
	// Jika Array 1 Tahun maka akan berisi 4 Digit
	if(va [0].length == 4 && va.length >= 3){
		cRetval = va [2] + "-" + va [1] + "-" + va[0] ;
	}
	return cRetval ;
}

function ShowAlert(){}

function isDateValided(date){
	return a.isDateValided(date) ;
};

// Ini Adalah Daftar Variable yang akan digunakan Secara Global Tidak boleh ada yang kembar.
function _grandWin(){
  let w = window ;
  let v = null ;
  for(let n = 0; n < 10;n++){
    if(w.name !== "mainFrame"){
      v = __getParent(w) ;
      w = v [0] ;
      if(!v [1]) n = 10 ;
    }
    if(w.name == "mainFrame") n = 10 ;
  }
  
  if(w.name !== "mainFrame"){
    let a = w.document.getElementById("mainFrame") ;
    if(a !== null) w = a.contentWindow ;
  }

  return w ;
} ;

function __getParent(o){
let o1 = o ;
let b = "" ;
let lRetval = true ;
  
  o = o.self.parent ;
  try {
    b = o.name ;
  } catch (e) {
    lRetval = false ;
    o = o1 ;
  }
  return [o,lRetval]
};

function submit(){
	document.form1.submit() ;
}

function generateCsrfToken(length = 32) {
  const array = new Uint8Array(length);
  window.crypto.getRandomValues(array); // Fill the array with cryptographically strong random values
	var csrfToken = Array.from(array, byte => byte.toString(16).padStart(2, '0')).join('');
  //writeCookie("csrfToken",csrfToken,0.5) ;
	return csrfToken ;
}