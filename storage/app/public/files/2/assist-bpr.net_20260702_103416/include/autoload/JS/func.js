function padl(cValue,nLen,cChar){
  var cRetval = "" ;
  var cLen = "0000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000000" ;   
  
  cLen = cLen.substring(0,nLen)  ; 
  cRetval = cLen.substring(0,nLen - cValue.length) + cValue ;
  return cRetval ;
}

function SumSetorTarik(mutasi,cDK,nSaldoAwal,nSaldoAkhir){
	if(mutasi.value != 0){
		var nSaldoHitung = nSaldoAwal;
		var nTotal = 0 ;
		if (nSaldoHitung.includes("(")) {
			nSaldoHitung = -parseFloat(String2Number(nSaldoHitung));
		}
		if(cDK == "D"){
			nTotal = parseFloat(String2Number(nSaldoHitung)) + parseFloat(String2Number(mutasi.value));
		}else{
			nTotal = parseFloat(String2Number(nSaldoHitung)) - parseFloat(String2Number(mutasi.value));
		}
		if(nTotal < 0){
			mutasi.value = ZFormat(nSaldoAwal);
			nTotal 			 = 0
		}
		nSaldoAkhir.value = ZFormat(nTotal);
	}else{
		nSaldoAkhir.value = ZFormat(nSaldoAwal);
	}
}

function padlAll(cField){
  var cRetval = "" ;
  cRetval     = padl(cField.value,cField.maxLength,"0") ;    
  return cRetval ;    
}

function showField(NameClass,lShow=true){
	var Show = (lShow) ? "block" : "none" ;
	var Ref = document.getElementsByClassName(NameClass) ;
	for (var i = 0; i < Ref.length; i++) {
		Ref[i].style.display = Show ;
	}
}

function ZFormat(nNumber,nDecimals){   
  var n         = 0 ;
  var cNumber   = "" ;
  var cDigit    = "" ;
  var nDigit    = 0 ;
  var cRetval   = "" ;
  var nLen      = 0 ;
  var i         = 0 ;
  var cSplit    = "" ;
  nCount        = "00000000000000000000000000000" ;
  nCountDefault = nCount ;
   
  //Default 
  if(ZFormat.arguments.length == 1){  
    nDecimals = 2 ;
  } 
  
  //Jika Kosong    
  if(nNumber == ""){
    cRetval = "0" ;
    if(nDecimals > 0) cRetval = cRetval + "." + nCount.substring(0,nDecimals) ;
    return cRetval ;
  }
    
  nCount  = "1" + nCount.substr(0,nDecimals) ;
  nCount  = parseFloat(nCount) ;  
  n       = Math.round(String2Number(nNumber) * nCount) ;
  n       = n / nCount ;  
  cNumber = n.toString() ;  
  nDigit  = cNumber.indexOf(".",1) ;
  //Periksa Apakah Ada Koma Untuk Bilangan Tersebut
  if(nDigit < 0){
    if(nDecimals > 0){
      cDigit = "." + nCountDefault.substr(0,nDecimals) ;
    }else{  
      cDigit = "" ; 
    }  
  }else{
    cDigit  = cNumber.substring(nDigit) ;
    cNumber = cNumber.substring(0,nDigit) ;    
  } 
  
  cRetval = "" ;
  nLen = cNumber.length ;
  for(i=nLen-3;i>-3;i-=3){
    cSplit = cNumber.substring(i,i+3) ;    
    if (cSplit !== ""){
      cRetval =  cSplit + "," + cRetval ;
    }
  }
  cRetval = cRetval.substring(0,cRetval.length -1) ;
  return cRetval + cDigit ;
}



// Kita akan cek apakah dia di bolehkan bertransaksi dengan cara melihat divIsOpenTransaksi
// kalau ada kita cek kalau status=0 boleh transaksi, kalau tidak 0 tidak boleh transaksi
function CheckOpenTransaksi(){
	let div = a.getById("divIsOpenTransaksi") ;
	if(div !== null){
		var va = a.str2JSON(div.innerText) ;
		va = va.getRow ;
		if(typeof va.Error !== "undefined"){
			if(va.Error !== 0){
				// Siapkan Background Biar tidak bisa click component di bawah nya
				// Dan Kalau Background di click akan muncul snackBar pesan errornya
				let back = a.addBack() ;
				((va,back)=>{
					back.onclick = function(){frm.snackBar(va.Message,"error") ;} ;
				})(va,back)
				frm.snackBar(va.Message,"error") ;
			}
		}
		a.delObj(div) ;
	}
}