const _comConfig = (()=>{
	let _f = document.currentScript.src ;
	let _result = _f.indexOf("/ajax.");
	if(_result >= 0) _f = _f.substring(0,_result) ;
	let _l = window.location ;
	let _u = _l.href.lastIndexOf( '/' );
	let _h = _u >= 0 ? _l.href.substring(0,_u+1) : "" ;

	let vaPath = _f.split("/") ;
	let comFolder = "../" + vaPath [vaPath.length-1] ;
	let comType = "" ;
	if(typeof document.currentScript.attributes.compVersion !== "undefined"){
		comFolder = _f ; 
		comType = "mvc" ;
	}
	return {comFolder:comFolder,
					comType:comType,
					h:_h,
					l:_l,
					path:vaPath
				 } ;
})() ;

// Kalau window.name kosong maka kita isi root.
if(window.name == ""){
	window.name = "root" ;
}

const __COMPONENT_FOLDER__ = _comConfig.comFolder ;
const __COMPONENT_TYPE__ = _comConfig.comType ;
const __BASE_URL__ = getattr("baseURL",_comConfig.h) ;
const __APP_ID__ = getattr("appid","") ;
//const __CSRF_TOKEN__ = getattr("csrftoken","") ;
const __COMP_URL__ = getattr("compURL",_comConfig.l.origin + "/" + _comConfig.path [_comConfig.path.length-1] + "/") ;
const __DIR_URL__ = getattr("dirURL","") ;
const __CONTROLLER__ = getattr("controllerURL","") ;
const __COMPONENT_VERSION__ = getattr("compVersion","1.0") ;
// Kalau Non MVC kita akan load Satu per satu file js nya, tapi kalau sudah mvc dia akan di load bersamaan untuk menghemat waktu dan dilakukan di php bukan disini
if(__COMPONENT_TYPE__ != "mvc"){
	document.write("<script id='mainScript_all' type='text/javascript' src='" +  compFolder() + "/ajax.php'></script>") ;	
}

function compFolder(){return __COMPONENT_FOLDER__ ;}
function getattr(attr,cDefault){
	let o = eval("document.currentScript.attributes." + attr) ;
	if(typeof o !== "undefined"){
		cDefault = o.nodeValue ;
	}
	return cDefault ;
}
