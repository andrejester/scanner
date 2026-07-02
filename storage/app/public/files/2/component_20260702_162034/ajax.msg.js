const msg = {
	lShow:false,cTitle:"",_w:0,
	oDiv:{divMain:null,divWait:null},
  focus: function(){
    var o = a.getById("_bwait") ;
    if(o !== null) o.focus() ;
  },
  unload: function(field){
    msg.focus() ;
    msg.bClick();
  },
  waitStart : function (nTimeout=0,cTitle=""){
    var nW = document.body.offsetWidth ;
    msg.cTitle = "Tunggu Sebentar, Data sedang diproses....." ;
		if(cTitle !== "") msg.cTitle = cTitle ;
    msg.lShow = false ;
		let div = a.addBack("_oWait") ;
		with(div.style){
			opcity = 0.1 ;
			cursor = "wait" ;
		}
		((div)=>{
			div.onclick = ()=>{
				msg.bClick();
			} ;
			
			div.Button.onblur = ()=>{
				msg.unload(div.Button) ;
			} ;
			
			div.Button.onclick = ()=>{
				msg.bClick();
			} ;
		})(div) ;		
		msg.oDiv.divMain = div ;
		
    msg.stopWait();
    if(nTimeout !== 0){
      msg._w = setTimeout(msg.waitEnd, nTimeout * 1000,this,1) ;
    }
		return div ;
  },  
  waitEnd: function(o,nTimeout){
    if(o == null) o = this ;
    o.stopWait() ;
    if(nTimeout !== null && typeof nTimeout !== 'undefined' && typeof waitTimeout == "function") waitTimeout() ;
		for(const key in o.oDiv){
			a.delObj(o.oDiv[key]) ;
		}
  },
  stopWait: function(){
    if(msg._w !== 0) clearTimeout(msg._w);
  },
  bClick: function(){
    if(msg.lShow) return true ;
    msg.lShow = true ;
    msg.focus() ;
    msg.oDiv.divWait = a.getById("divWait") ;
    if(msg.oDiv.divWait == null){
      msg.oDiv.divWait = a.addObj("div",null,"divWait","divWait",null,msg.cTitle.replace(" ","&nbsp;")) ;
    }
		a.setObjIndex(msg.oDiv.divWait) ;
  },

	// Show Alert, Confirm
  _show(obj){
		let cMessage = obj.data.message ;
		let title = obj.data.title ;
		let type = obj.data.type ;
		if(title == null || title == "") title = type ;
		
		let id = Math.floor(Math.random() * 1000000);
		
		// Inisialisasi Variable
		let oData ={back:null,win:null,ButonPos:null,input:null,cmdOk:null,cmdCancel:null,type:type,txtEdit:false,obj:obj} ;
    oData.back = a.addBack("_oBack_" + id,null,0) ;
		
		// Window Utama
		oData.win = a.addObj("div",null,"_alWindows_" + id,"alr_main no_txt_select") ;

		// Parent -> Header 
		let oh = a.addObj("div",oData.win,"_divAlertHead_" + id,"alr_head") ;      

		// Parent -> header -> Title
		let ot = a.addObj("div",oh,null,"alr_title no_txt_select",null,title) ;

		// Event Move
		oData.win.onmousedown = (e)=>{
			a.obj_move_start(oData.win,e) ;
		} ;

		// Object Div Main
		let om = a.addObj("div",oData.win,"_divAlertMsg_" + id,"alr_msg no_txt_select") ; //,null,cMessage) ;
		om.align = "left" ;

		// Parent -> Message
		if(type !== "Prompt"){
			om.innerHTML = cMessage ;
		}else{
			oData.input = a.addObj("input",om,null,null,"width:100%") ;
			oData.input.type = "text" ;
			oData.input.value = cMessage ;
			((oData,input)=>{
				input.onkeydown = ()=>{msg.txtKeyDown(input,oData)} ;
				input.onblur = ()=>{msg.txtEdit(false,oData)} ;
				input.onfocus = ()=>{msg.txtEdit(true,oData)} ;
				input.onmousedown = ()=>{msg.txtEdit(true,oData)} ;
			})(oData,oData.input) ;
		}

		// Button Parent
		let ob = a.addObj("div",oData.win,"_divAlertButton_" + id,"alr_button no_txt_select") ;
		ob.align = "center" ;

		// Add Button OK
		const divOk = a.addObj("div",ob,null,"div-input-container") ;
		const cmdOK = a.addObj("input",divOk,"_AlertButton_" + id,"Button") ;
		cmdOK.value = "OK" ;
		cmdOK.type = "button" ;
		cmdOK.name = "cmdSave" ;
		oData.cmdOk = cmdOK ;
		oData.ButonPos = cmdOK ;
		((button,oData)=>{
			button.onkeydown = ()=>{return msg._kd(1,event,oData)} ;
			button.onclick = ()=>{msg.alrClick(true,oData)} ;
		})(cmdOK,oData) ;
		a.setButtonIcon(cmdOK) ;

		// Add Button Cancel
		if(type == "Confirm" || type == "Prompt"){
			const divCancel = a.addObj("div",ob,null,"div-input-container") ;
			const cmdCancel = a.addObj("input",divCancel,"_AlertCancel_" + id,"Button") ;
			cmdCancel.value = "Cancel" ;
			cmdCancel.type = "button" ;
			cmdCancel.name = "cmdCancel" ;
			oData.cmdCancel = cmdCancel ;
			((button,oData)=>{
				button.onkeydown = ()=>{return msg._kd(2,event,oData)} ;
				button.onclick = ()=>{msg.alrClick(false,oData)} ;
			})(cmdCancel,oData) ;
			a.setButtonIcon(cmdCancel) ;			
		}

		let w = window.innerWidth ; 
    let h = window.innerHeight ;
    h = Math.max(0,(h/2) - (oData.win.offsetHeight/2)) ;
    w = Math.max(0,(w/2) - (oData.win.offsetWidth/2)) ;
    msg.tout(oData);    
    with(oData.win.style){
			top = h ;
			left = w ;
		}

		if(type == "Prompt") fieldfocus(oData.input) ;
    setObjIndex(oData.win) ;
  },
	txtKeyDown(f,oData){
		if(event.keyCode == 13){
			msg.alrClick(true,oData) ;
		} else if(event.keyCode == 27){
			msg.alrClick(false,oData) ;
		} 
	},
  // KeyDown untuk Alert/Confirm hanya tombol Panah tertentu yang boleh, selainnya kita matikan
  _kd:function(n,e,oData){
    var num = e.keyCode ;
    if(num == 37 || num == 38){
      n -- ;
      if(n == 0) n = 2 ;
    }else if(num == 39 || num == 40){
      n ++ ;
      if(n == 3) n == 1 ;
    }else if(num == 13){
      return true ;
    }else if(num == 27){
			msg.alrClick(false,oData) ;
		}
    if(oData.type == "Alert") n = 1 ;		
    oData.ButonPos = (n == 2) ? oData.cmdCancel : oData.cmdOk ;
		oData.ButonPos.focus() ;
    return false ;
  },
  txtEdit:function(lPar,oData){
    oData.txtEdit = lPar ;
  },
  tout(oData){
		if(oData.win !== null){
			if(oData.txtEdit == false) oData.ButonPos.focus() ;
    	setTimeout(msg.tout,50,oData) ;
		}    
  },
  alrClick: function(par,oData){
    // Jika Prompt Dan Hasil true maka kita isi dengan Input Box selain itu null
    if(oData.type == "Prompt"){
      par = par ? oData.input.value : null ;
    }
		oData.win.classList.add("alr_close") ;
		setTimeout(()=>{
			a.delObj(oData.back);
			a.delObj(oData.win) ;
			oData.win = null ;

			// callBack
			frm.responseMessage(par,oData.obj["par"]["caller"],oData.obj["par"]["call_id"]) ;
		},200,oData,par) ;
  }	
}