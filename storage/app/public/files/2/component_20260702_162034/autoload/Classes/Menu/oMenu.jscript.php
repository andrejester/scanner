<?php
	include 'df.php' ; 
	$cDir = __DIR__ ;
	$cRoot = Svr::GetDocumentRoot() ;
	$cDir = Svr::IsMVC() ? "" : "../" . substr($cDir,strlen($cRoot)+1) ;
?>
<script language="javascript" type="text/javascript">
// Menu Horizontal
const hMenu = {
	_dir_:"<?php echo($cDir) ?>",lMVC:<?php echo(Svr::IsMVC() ? "true" : "false") ?>,baseURL:"<?php echo(MVC::GetBaseURL()) ?>",
  lMouseOver:false,lClick:false,lMultiFrame:true,lMultiForm:false,nMenuTop:0,nMenuLeft:0,
  oWinTop:null,__cMenu:'',cMenuOut:"",button:null,mnuClick:null,lhzKey:false,mnuvrClick:null,lvrKey:false,MnuID:"",oldMnuID:"",
  hzmenuOut: function(menu,cMenuID){this.lMouseOver = false;if(!this.lClick){menu.className="menu_hrz_out no_txt_select"}},
  hzwinClick: function(){if(!this.lMouseOver && this.lClick){this.closeallMenu()}},
  UpdMenuOpen: function(cMenuID){if(this.__cMenu.indexOf(cMenuID+',')==-1){this.__cMenu += cMenuID + ","}},
  getParent: function(cMenuID){var n = cMenuID.lastIndexOf("."); return cMenuID.substring(0,n)},
  /*
	mnuSaveLog: function(cMenuNumber,cMenuTitle){
		let _url = this.lMVC ? (this.baseURL + "component/") : (this._dir_ + "/../../../") ;
		//a.ajax(_url + 'menu/mmenu.ajax.php','MenuSaveLog()','cMenuNumber=' + cMenuNumber.replace(/&nbsp;/g,' ') + '&cMenuTitle=' + cMenuTitle.replace(/&nbsp;/g,' ')) ;
		a.ajax(_url + "/savelog","SaveLog()","cMTI=03&cMessage="+cMenuTitle.replace(/&nbsp;/g,' ')+" - "+cMenuNumber.replace(/&nbsp;/g,' ')) ;
	},
	*/
  init: function(cMenu){
    var va = cMenu.split(",") ;
    for(var n = 0 ; n < va.length ;n++){
      var cSub = "__cMenuID-" + va [n] ;
      var mn = a.getById("cell-" + cSub) ;
      if(mn !== null){
        (function(n,mnu,cSub,mn){
          mn.onmouseover = function(){mnu.hzmenuOver(this,cSub);} ;
          mn.onclick = function(){mnu.hzmenuClick(this,cSub);} ;
          mn.onmouseout = function(){mnu.hzmenuOut(this,cSub);} ;
        })(n,this,cSub,mn) ;
      }
    }

    // Buat Div Background Kalau di click di luar Menu
    var ob = a.addObj("div",null,"idMainMenu",null,"position:absolute;display:none") ;
    (function(mnu){      
      ob.onclick = function(){mnu.disableButton();mnu.closeallMenu();} ;
      window.onkeydown = function(e){mnu.winKeyDown(e);} ;

      // Jika Ada iframe mainFrame maka keydown juga kita ambil
      var o1 = a.getById("mainFrame") ;
      if(o1 !== null) o1.contentWindow.onkeydown = function(e){mnu.winKeyDown(e);} ;

      // Jika di click di luar Menu Horizontal maka kita close    
      var om = a.getById("hzMainMenu") ;
      if(om !== null) om.onclick = function(){if(!mnu.lMouseOver) mnu.closeallMenu();} ;
      
      var oldLoad = window.onload ;
      window.onload = function(){
        if(typeof oldLoad == "function") oldLoad();
        var _om = a.getById("hzMainMenu") ;
        if(_om !== null){
          // Buat Button Untuk Menunggu Keyboard supaya bisa di buat Shortcut
          mnu.button = a.addObj("input",_om) ;
          mnu.button.type = "button" ;
          mnu.button.disabled = true ;
          mnu.button.style.cssText = "width:1px;height:1px;opacity:0;" ;
        }
      } ;
    })(this);
  },
  winKeyDown: function(e){
    var key = txt.keyNum(e) ;
    if(e.altKey){
      if(key >= 49 && key <= 57){
        var oNew = a.getById("cell-__cMenuID-" + String.fromCharCode(key)) ;
        if(oNew.cellIndex !== null){
          var cNewSub = oNew.id.replace("cell-","") ;
          this.hzmenuClick(oNew,cNewSub) ;
        }
      }
    }else if(e.ctrlKey){
      if(key == 192){
        var oMnu = a.getById("table-menu-horizontal") ;
        if(oMnu !== null && oMnu.rows.length > 0 && oMnu.rows[0].cells.length > 0){
          var oNew = oMnu.rows[0].cells[0] ;
          if(oNew !== null){
            var cNewSub = oNew.id.replace("cell-","") ;
            this.hzmenuClick(oNew,cNewSub) ;
          }
        }
      }
    }
  },
  disableButton: function(){
    if(this.button !== null){
      this.button.disabled = true ;
    }
  },
  hzmenuClick: function(menu,cMenuID){
    this.lClick=true ;
    this.hzmenuOver(menu,cMenuID) ;

    if(this.button !== null){
      this.button.disabled = false ;
      this.lhzKey = true ;
      this.lvrKey = false ;
      this.button.focus() ;
      
      // Setting Event Key Untuk Keyboard
      var mnu = this ;
      this.button.onkeydown = function(e){return mnu.waitKeyPress(e);} ;
    }
  },
  movehzMenu: function(key,vaMenu){
    var old = a.getById("cell-__cMenuID-" + vaMenu [0]) ;
    var nCol = old.cellIndex ;
    var nMaxCol = old.parentNode.cells.length ;
    var cSub = old.id.replace("cell-","") ;

    // Panah Kanan
    if(key == 39){
      nCol ++ ;    
      if(nCol >= nMaxCol) nCol = 0 ;
    }

    // Panah Kirim
    if(key == 37){
      nCol -- ;
      if(nCol < 0) nCol = nMaxCol - 1 ;
    }

    // Panah Bawah
    if(key == 40) vaMenu [1] = 0 ;

    var oNew = old.parentNode.cells[nCol] ;
    var cNewSub = oNew.id.replace("cell-","") ;
    if(old.cellIndex !== oNew.cellIndex){
      this.hzmenuOut(old,cSub) ;
      this.hzmenuOver(oNew,cNewSub) ;
    }
    return vaMenu ;
  },
  movevrMenu: function(key,vaMenu,oPar){
    var cMenuID = "" ;
    var cOldMenuID = "" ;
    for(var n=0;n<vaMenu.length;n++){
      if(cMenuID !== "") cMenuID += "." ;
      if(cOldMenuID !== "") cOldMenuID += "." ;
      cOldMenuID += vaMenu [n] ;

      if(n > 0){
        var oldMnu = a.getById("menuid-" + cOldMenuID) ;
        var oMnu = oldMnu ;
        var vaMnuItem = {} ;
        var currRow = 0 ; //n ;
        if(oldMnu !== null){
          var vaConf = oldMnu.cells [1].textContent.split("|") ;
          this.vrmenuOut(oldMnu,vaConf [2],vaConf[1]) ;
          
          // Kita Masukkan Item Khusus Untuk Item Terakhir
          if(n + 1 >= vaMenu.length){
            var tb = oldMnu.parentNode ;
            var x = 0 ;
            for(var i=0;i<tb.rows.length;i++){
              if(oldMnu.rowIndex == i) currRow = x ;
              if(tb.rows[i].className.indexOf("menu_vert_item_sep") < 0){
                vaMnuItem [x] = tb.rows[i] ;
                x ++ ;
              }
            }
            
            x -- ;
            if(key == 40){
              // Panah Turun
              currRow ++ ;
              if(currRow > x) currRow = 0 ;
            }else if(key == 38){
              // Panah Naik
              currRow -- ;
              if(currRow < 0) currRow = x ;
            }
            oMnu = vaMnuItem [currRow] ;
          }
        }else{
          // Kalau Old Menu Kosong Bararti kita ambil element Pertama Dari Menu Itu karena Menu baru dibuka
          var tb = a.getById("table-__cMenuID-" + this.getParent(cOldMenuID)) ;
          for(var i=0;i<tb.rows.length;i++){
            if(tb.rows[i].className.indexOf("menu_vert_item_sep") < 0){
              oMnu = tb.rows[i] ;
              i = tb.rows.length ;
            }
          }
        }

        if(oMnu !== null){
          cMenuID = oMnu.id.replace("menuid-","") ;
          var vaConf = oMnu.cells [1].textContent.split("|") ;
          this.vrmenuOver(oMnu,vaConf [2],vaConf[1]) ;
          oPar ["cSub"] = vaConf [1] ;

          var oDiv = a.getById(this.getParent("__cMenuID-" + cMenuID)) ;
          if(oDiv !== null){
            if(oMnu.offsetTop < oDiv.scrollTop) oDiv.scrollTop = oMnu.offsetTop ;
            if(oMnu.offsetTop + oMnu.offsetHeight > oDiv.scrollTop + oDiv.clientHeight) oDiv.scrollTop = oMnu.offsetTop - oDiv.clientHeight + oMnu.offsetHeight ;
          }
        }
      }
    }
    this.MnuID = cMenuID ;
    if(key == 13 && oMnu.onclick) oMnu.onclick() ;
  },
  waitKeyPress: function(e){
    var key = txt.keyNum(e) ;
    var vaMenu = this.MnuID.split(".") ;

    if(key == 27){      
      this.closeallMenu() ;
      return false ;
    }

    // Jika Menu Level 1 maka pergerakan pada posisi Menu Horizontal
    if(vaMenu.length == 1) vaMenu = this.movehzMenu(key,vaMenu) ;
    
    // Jika Level Lebih dari 1 maka kita buka sub Menu
    var oPar = {"cSub":0} ;
    if(vaMenu.length > 1){
      // Move pada posisi Vertical Menu
      this.movevrMenu(key,vaMenu,oPar) ;
      var cMenuID = this.MnuID ;
      if(key == 39 || key == 37){
        var nMenuLevel = this.mnuLevel(cMenuID) ;
        if(nMenuLevel == 2 && (oPar ['cSub'] == "0" || key == 37)){
          // Jika Menu Level 2 dan Panah Kiri maka kita geser Horizontal Menunya
          cMenuID = this.getMnuLevel(cMenuID,1) ;
          this.movehzMenu(key,cMenuID.split(".")) ;
        }else{
          // Jika Panah Kiri maka kita naik ke level sebelumnya
          if(key == 37){
            // Kita Out dulu pada posisi Menu Level paling bawah karena kita akan naik ke atas nya
            var oldMnu = a.getById("menuid-" + cMenuID) ;
            if(oldMnu !== null){
              var vaConf = oldMnu.cells [1].textContent.split("|") ;
              this.vrmenuOut(oldMnu,vaConf [2],vaConf[1]) ;
              this.closeallMenu(cMenuID,"__cMenuID-" + cMenuID,true) ; 
            }
        
            cMenuID = this.getMnuLevel(cMenuID,(nMenuLevel-1)) ;
            this.movevrMenu(key,cMenuID.split("."),oPar) ;
          }else if(key == 39 && oPar ['cSub'] == "1"){
            cMenuID += ".1" ;
            this.movevrMenu(key,cMenuID.split("."),oPar) ;
          }          
        }
      }
    }
    return false ;
  },
  getMnuLevel: function(cMenuID,nLevel){
    var vaMenu = cMenuID.split(".") ;
    var cRetval = "" ;
    for(var n=0;n<vaMenu.length;n++){
      if(n < nLevel){
        if(cRetval !== "") cRetval += "." ;
        cRetval += vaMenu [n] ;
      }
    }
    return cRetval ;
  },
  mnuLevel: function(cMenuID){
    return cMenuID.split(".").length ;
  },
  hzmenuOver: function(menu,cMenuID){
    this.mnuClick = menu ;
    this.showVMenu(menu,cMenuID);
    menu.className = "menu_hrz_over no_txt_select" ;
    this.lMouseOver = true ;
    this.MnuID = menu.id.replace("cell-__cMenuID-","") ;
  },
  _ohm: function(){
    var ohm = null ;
    var oM = a.getById("mainFrame") ;
    if(oM == null){
      this.lMultiFrame = true ;
			let wParent = self.parent ;
      ohm = a.getById('mainFrame',wParent).contentWindow ;
    }else{
      ohm = window ;
      this.lMultiFrame = false ;
    }
    return ohm ;
  },
  screenSize: function(cWinID){
    var o = a.getById(cWinID) ;
    var h = 500 ;
    var w = 1000 ;
    if(o !== null){
      h = Math.max(o.contentWindow.innerHeight-4,h) ;
      w = Math.max(o.contentWindow.innerWidth-4,w) ;
    }
    return [w,h] ;
  },
  initSubMenu: function(cMenuID){
    var ot     = a.getById("table-" + cMenuID) ;
		var _dir_  = "<?php echo($cDir) ?>"
		if(ot !== null){
      for(var n=0 ; n< ot.rows.length ;n++){
        if(ot.rows [n].cells [1].textContent.substring(0,6) !== "reset|" && ot.rows[n].className !== "menu_vert_item_sep"){
          var va = decodeURIComponent(ot.rows [n].cells [1].textContent).split(",") ;
          (function(n,mnu,va,row){
            row.onmouseover = function(){mnu.vrmenuOver(this,va [1],va [0])} ;
            row.onmouseout = function(){mnu.vrmenuOut(this,va [1],va [0])} ;
            
            // Jika Tidak memiliki Sub Menu maka dia akan membuka Form
            // Col = "sub","sub-id","url","cFunc","frmName","frmTitle","nWidth","nHeight","menuNumber","host" ;
            //         0       1      2       3       4         5          6        7          8         9
            if(va [0] == 0){
              row.onclick = function(){
								gData.Save(["mnuClick"],va [8]) ;
                //mnu.mnuSaveLog(va [8],va [5]) ;
								
                mnu.closeallMenu() ;
                
                // Jika Width atau height menggunakan @WIDTH dan @HEIGHT maka di isi Screen Size
                try {
                  if(va [6].toUpperCase().indexOf("@WIDTH") >= 0 || va [7].toUpperCase().indexOf("@HEIGHT") >= 0){
                    vaSize = mnu.screenSize("mainFrame") ;                        
                    va [6] = eval(va [6].toUpperCase().replace("@WIDTH", vaSize [0])) ;
                    va [7] = eval(va [7].toUpperCase().replace("@HEIGHT", vaSize [1])) ;
                  }                
                }catch (e){}
								
								var hostname = va[9].split("/") ;
                if(va [9] !== "localhost"){
									var protocol = window.location.protocol + "//" ;
									if(hostname[0] === location.hostname){
										let _url = this.lMVC ? (this.baseURL + "component/") : (this._dir_ + "/../../../") ;
										a.ajax(_url + 'menu/mmenu.ajax.php','loadSublocal()','cMenuNumber=' + va [8].replace(/&nbsp;/g,' ') + '&cMenuTitle=' + va [5].replace(/&nbsp;/g,' ') + '&cHost=' + va[9] + '&cParam=' + va[2], function(cData){
											var vaData = JSON.parse(cData) ;
											frm.open(protocol + va [9] + "/main.php?__par=" + vaData.par +"&Mnu=" + vaData.menu+ "&MnuTitle=" + vaData.mnutitle,va [4], va [5],va [6],va [7],'',false,'no',false,'mainFrame','',va[8]) ;
										}) ;
									}else{
										frm.open("../component/comp.submain.php?cHost=" + va [9] + "&__par=" + va [2] +"&Mnu=" + va [8]+ "&MnuTitle=" + va [5],va [4],va [5],va [6],va [7],'',false,'no',false,'mainFrame','',va[8]) ;
									}
                }else{
									let _va1 = va [3].split("?") ;
									va [3] = _va1[0] ;
                  if(eval("typeof " + va [3] + "_onClick") == 'function'){
                    eval(va [3] + "_onClick(this)") ;
                  }else{
                    var i;
                    for (i = 0; i < va.length; i++) {
                      va [i] = va [i].replace(/&nbsp;/g,' ') ;
                    }
										let _frmurl = mnu.lMVC ? va[2] : "main.php?__par=" + va [2] ;
                    frm.open(_frmurl,va [4], va [5],va [6],va [7],'',false,'no',false,'mainFrame','',va[8]) ;
                  }
                }
              }
            }
          })(n,this,va,ot.rows [n]) ;
          
          // Kita Isi Untuk keperluan Keyboard
          ot.rows [n].cells [1].textContent = "reset|" + va [0] + "|" + va [1] ;
        }
      }
    }
  },
  showVMenu: function(menu,cMenuID){
    // Inisialisasi Event Menu
    this.initSubMenu(cMenuID) ;
    this.initTableMenu(cMenuID) ;
    this.closeallMenu(cMenuID) ; 
    this.UpdMenuOpen(cMenuID);
    if(this.lClick){
      this.cMenuOut = "" ;
      var om = a.getById(cMenuID);
      var nMTop = menu.offsetHeight + menu.offsetTop ;
      if(om == null){
        var nMLeft = menu.offsetLeft + document.body.scrollLeft ;
        this.CreateMenu(cMenuID,nMTop,nMLeft) ;
      }else{
        if(!this.lMultiFrame) nMTop = 22 ;
        with(om.style){
          display = "block" ;
          left = menu.offsetLeft + document.body.scrollLeft ; 
          top = nMTop ;
          height = "auto" ;
          height = Math.min(om.clientHeight,window.innerHeight-om.offsetTop-5) ;
        }
        this.setMainMenu(true) ;
        setObjIndex(om) ;
      }
    }
  },
  CreateMenu: function(cMenuID,nTop,nLeft){    
    var ot = a.getById(cMenuID);
    var om = a.addObj("div");    
    om.id = cMenuID ;
    with(om.style){display="block";position="absolute";top=nTop;left=nLeft}
    om.innerHTML = ot.innerHTML ;
    var otb = a.getById("table-"+cMenuID) ;
    with(om.style){height=otb.offsetHeight+4;width=otb.offsetWidth+4}

    var osb = a.getById("shadowBottom-"+cMenuID).style ;    
    osb.top = parseFloat(otb.offsetHeight) ;
    osb.width = parseFloat(otb.offsetWidth)-1 ;

    var osr = a.getById("shadowRight-"+cMenuID).style ;
    osr.left=parseFloat(otb.offsetWidth) ;
    osr.height=parseFloat(otb.offsetHeight) - 4 ;

    setObjIndex(om) ;
    return om ;
  },
  topWin: function(){
    var oM = a.getById("mainFrame");
    if(oM == null){
      this.oWinTop = a.getById("topFrame",self.parent) ;
      if(this.oWinTop !== null){
        this.oWinTop = this.oWinTop.contentWindow ;
        this.lMultiForm = true ;
      }else{
        this.lMultiForm = false ;
        this.oWinTop = window ;
      }
    }else{
      this.lMultiForm = false ;
      this.oWinTop = window ;
    }
    return this.oWinTop
  },
  openSubMenu: function(menu,cMenuID){
    var nSH = window.innerHeight ;
    var nST = document.body.scrollTop ;
    var nSW = window.innerWidth ;
    var nSL = document.body.scrollLeft ;

    this.initSubMenu(cMenuID) ;
    this.initTableMenu(cMenuID) ;
    this.setMainMenu(true) ;
    var om = a.getById(cMenuID);
    if(om == null){
      om = this.CreateMenu(cMenuID,this.nMenuTop,this.nMenuLeft-4) ;
    }
    setObjIndex(om) ;
    this.UpdMenuOpen(cMenuID) ;
    om.style.display = "block" ;
    om.style.height = "auto" ;

    // Menu Height maximum setinggi Windows  Height
    om.style.height = Math.min(om.clientHeight,nSH-6) ;  

    if(om.offsetHeight+this.nMenuTop > nSH+nST) {this.nMenuTop = nSH+nST-om.offsetHeight-2}
    if(om.offsetWidth+this.nMenuLeft > nSW+nSL) {this.nMenuLeft = nSW+nSL-om.offsetWidth-2}

    with(om.style){left=this.nMenuLeft-4;top=this.nMenuTop}
  },
  closeallMenu: function(cMenuID,cPrefix,lnotClose){
    var va = this.__cMenu.split(",") ;
    if(arguments.length < 1){
      cMenuID = "" ;
      this.lClick = false ;
      this.disableButton() ;
    }
    if(arguments.length < 2){cPrefix=""}
    cMenuParent = this.getParent(cMenuID) ;
    this.__cMenu = "" ;
    for(n=0;n<va.length;n++){
      cID = va[n] ;
      if(cID !== "" && cID !== null){
        if(cID !== cMenuParent && cID.substring(0,cPrefix.length) == cPrefix){
          var om = a.getById(cID) ;
          if(om !== null){om.style.display = "none"}

          var oc = a.getById("cell-"+cID) ;
          if(oc!== null){oc.className="menu_hrz_out no_txt_select"}
        
          cP = this.getParent(cID) ;
          if(cP!==""){this.initTableMenu(cP)}
        }else{
          this.__cMenu += va[n]+"," ;
        }
      }    
    }
    var om = a.getById("idMainMenu") ;
    if(om !== null && !lnotClose) om.style.display = "none" ;
  },
  initTableMenu: function (cMenuID){
    var otb = a.getById("table-"+cMenuID);
    if(otb!==null){
      for(var n=0;n<otb.rows.length;n++){
        if(otb.rows [n].className !== "menu_vert_item_sep"){
          otb.rows[n].className = "menu_vert_item no_txt_select" ;
        }
      }
    }
  },
  setMainMenu: function(lShow){
    if(typeof this.lMultiForm !== "boolean" || this.lMultiForm) return true ;
    var om = a.getById("idMainMenu") ;
    if(om !== null){
      if(lShow){
        if(om.style.display == "none"){
          with(om.style){
            display = "block" ;
            top = 24 ;
            left = 0 ;
            width = document.body.scrollWidth - 2 ;
            height = document.body.scrollHeight - 26 ;
            zIndex = 0 ;
          }
        }
      }else{
        om.style.display = "none" ;
      }
    }
  },
  vrmenuOut: function(menu,cMenuID,nSubMenu){
    this.lMouseOver = false ;
    if(nSubMenu == 0){
      menu.className = "menu_vert_item no_txt_select" ;
    }else{
      this.cMenuOut = cMenuID ;
    }
  },
  vrmenuOver: function(menu,cMenuID,nSubMenu){
    if(this.cMenuOut !== cMenuID){
      this.lMouseOver = true ;
      this.closeallMenu(cMenuID,this.getParent(cMenuID),true) ; 
      if(this.cMenuOut !== "" && this.getParent(this.cMenuOut) == this.getParent(cMenuID)){
        this.cMenuOut = "" ;
      }
      if(nSubMenu == 1){
        this.getMenuPos(menu,cMenuID) ; 
        this.openSubMenu(menu,cMenuID)
      } ;
      menu.className = "menu_vert_item no_txt_select" ;
    }
    menu.className = "menu_vert_item menu_vert_item_over no_txt_select" ;
    this.MnuID = menu.id.replace("menuid-","") ;
  },
  getMenuPos: function(menu,cMenuID){
    var cMenuParent = this.getParent(cMenuID) ;
    var o = a.getById("table-"+cMenuParent) ;
    var od = a.getById(cMenuParent) ; 
    var nTop = menu.offsetTop ;
    this.nMenuTop = od.offsetTop + nTop - 1 - od.scrollTop ;
    this.nMenuLeft = od.offsetLeft + od.offsetWidth ;
  }
};
</script>