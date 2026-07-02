<?php include 'df.php' ; ?>
<script language="javascript" type="text/javascript">
function Form_onLoad(){
	let div = a.getById("divTree") ;
	let obj = a.var2Data(div.textContent) ;
	div.textContent = "" ;
	div.style = "display:block;height:480px;width:100%;overflow-y: auto;overflow-x:auto" ;
	//div.style.maxHeight = div.clientHeight ;
	a.addObj("div",div,null,null,null,"MENU") ;
	subMenu(div,obj.data,"ulMain","liMain") ;
}

function TogleCheck(div,checked=null){
	if(div == null || typeof div.checked == "undefined") return null ;
	
	if(checked == null) checked = div.checked ? false : true ;
	div.checked = checked ;
	div.innerHTML = div.checked ? "☑" : "☐" ;
}

function TogelExpand(div){
	let li = div.parentElement.parentElement ;
	let ul = li.querySelector('ul');
	div.expand = !div.expand ;
	div.innerHTML = div.expand ? "⊖" : "⊕" ;
	ul.style.display = div.expand ? "block" : "none" ;
}

function TogleSubMenu(div){
	if(div == null) return true ;
	
	let li = div.parentElement.parentElement ;
	let ul = li.querySelector('ul');
	if(ul !== null){
		let elements = ul.children ;
		for(const element of elements){
			// Kita Akan Setting Menu Di bawah nya
			TogleCheck(element.data.menu,div.checked) ;
			
			// Kalau dia ada sub menu juga kita bukan di bawahnya
			TogleSubMenu(element.data.menu) ;
		}		
	}	
}

// Membuat Sub Menu
function subMenu(parent,menu,cClass,liClass){
	let ul,li,div,row,lInduk,divExp ;
	ul = a.addObj("ul",parent,null,cClass) ;
	let nWidth = 0 ;
	
	const keysArray = Object.keys(menu);
	const lastKey = keysArray[keysArray.length - 1];
	for(const item in menu){
		li = a.addObj("li",ul,null,liClass) ;
		li.data = {id:menu[item].item.mnuID,old_id:menu[item].item.oldMnuID,menu:null,button:{}} ;
		if(liClass == "liSub"){
			div = a.addObj("div",li,null,"divTree",null) ;
			if(menu[item].item.mnuTitle !== "-") div.innerHTML = "─" ;

			if(item == lastKey){
				div.style.height = "12px" ;
			}
		} 
		
		lInduk = false ;
		// Untuk Checkbox		
		row = a.addObj("div",li,null,"divRow") ;		
		// Jika Separator Kita tidak isi bawah nya langsung lanjut
		if(menu[item].item.mnuTitle == "-"){
			row.className = "divRowSep" ;
			continue; 
		}else if(typeof menu[item].subMenu !== "undefined"){
			row.className += " divRow-induk" ;
			lInduk = true ;
		}
		
		// Untuk expand
		divExp = a.addObj("div",row,null,"divExp",null," ") ;
		if(typeof menu[item].subMenu !== "undefined"){
			// Jika punya sub menu kita beri tanda expand
			divExp.innerHTML = "⊖" ;
			divExp.expand = true ;
			divExp.style.cursor = "pointer" ;
			((div)=>{
				div.onclick = function(){
					TogelExpand(div) ;
				}
			})(divExp) ;
		}

		if(nWidth == 0) nWidth = li.clientWidth - 20 ;
		div = a.addObj("div",row,null,"divCheck",null,"☐") ;
		div.checked = false ;
		li.data.menu = div ;
		((div)=>{
			div.ondblclick = function(){
				// Kita akan cari kalau dia punya sub akan di update sub menu nya sama dengan induknya
				TogleSubMenu(div) ;
			}
			
			div.onclick = function(e){
				txt.canClick(div,500,()=>{
					TogleCheck(div) ;
				}) ;
			}
		})(div) ;
		
		// Title
		div = a.addObj("div",row,null,"divTitle",null,menu[item].item.mnuNumber + " " + menu[item].item.mnuTitle) ;
		if(lInduk){			// Jika Induk title bisa di click untuk expand
			div.style.cursor = "pointer" ;
			div.style.minWidth = row.clientWidth - div.offsetLeft - 20 ;
			((div,divExp)=>{
				div.onclick = function(){
					TogelExpand(divExp) ;
				}
			})(div,divExp) ;
		}

		// Jika ada Configurasi Tombolnya
		if(typeof menu[item].item.buttonList !== "undefined" && menu[item].item.buttonList.length > 0){
			// Membuat Check box cmdAdd,cmdEdit,cmdDelete
			var buttons = menu[item].item.buttonList ;
			for(let key = buttons.length-1; key >= 0; key--){
				li.data.button[buttons[key]] = a.addObj("div",row,null,"divButton",null,"☐") ;
				li.data.button[buttons[key]].checked = false ;
				((div)=>{
					div.onclick = function(){
						txt.canClick(div,500,()=>{
							TogleCheck(div) ;
						})
					} ;
				})(li.data.button[buttons[key]]) ;
				
				div = a.addObj("div",row,null,"divButtonCap",null,buttons[key].substring(3)) ;
			}
		}		

		if(typeof menu[item].subMenu !== "undefined"){
			subMenu(li,menu[item].subMenu,"ulSub","liSub") ;
		}
	}
}

function GetSaveMenu(){
	let vaMenu = {} ;
	let va,mnuID = null ;
	let vaLi = document.querySelectorAll("li");
	for (let li of vaLi) {
		if(typeof li.data !== "undefined"){
			var data = li.data ;
			if(data !== null && data.menu !== null && data.menu.checked){
				vaMenu[data.id] =  {"menu":true,"cmd":","} ;
				for(let key in data.button){
					if(data.button[key].checked){
						vaMenu[data.id].cmd += key + "," ;
					}
				}
			}
		}				
	}

	a.f.cMenu.value = JSON.stringify(vaMenu) ;
}

function cmdCancel_onClick(field){
	frm.close() ;
}

function cmdSave_onClick(field){
	GetSaveMenu() ;
	frm.save("","","",(obj)=>{
	}) ;
}

function cLevel_onButtonClick(field){
	field.Browse("SeekUserLevel","",(obj)=>{
		if(obj.dataRows == 0){
			a.f.cKeteranganLevel.value = "" ;
		}else{
			a.f.cKeteranganLevel.value = obj["Keterangan"] ;
			LoadUserLevel() ;
		}
	}) ;
}

function cLevel_onBlur(field){
	if(field.value == ""){
		a.f.cKeteranganLevel.value = "" ;
	}else{
		if(oldLevel !== field.value) cLevel_onButtonClick(field) ;
	}	
}

var oldLevel = "" ;
function LoadUserLevel(){
	if(a.f.cLevel.value !== ""){
		if(oldLevel !== a.f.cLevel.value){
			oldLevel = a.f.cLevel.value ;

			a.ajax("","GetMenuLevel()",a.f.cLevel,(obj)=>{
				SetupMenu(obj.getRow) ;
			}) ;
		}
	}
}

function SetupMenu(obj){
	let vaMenu = {} ;
	let va,mnuID = null ;
	let vaLi = document.querySelectorAll("li");
	for (let li of vaLi) {
		if(typeof li.data !== "undefined"){
			var data = li.data ;
			// Kita akan cek kalau data ada maka kita check kalau tidak maka uncheck
			var lChecked = false ;
			if(typeof obj[data.id] !== "undefined" || typeof obj[data.old_id] !== "undefined") lChecked = true ;
			TogleCheck(data.menu,lChecked) ;
			
			// Kita Akan Periksa Kalau dia ada option Button maka kita check juga
			// Kalau ada di database maka kita centang
			for(let key in data.button){
				lChecked = false ;
				if(typeof obj[data.id] == "string" && obj[data.id].includes("," + key + ",")){
					lChecked = true ;
				}
				TogleCheck(data.button[key],lChecked) ;
			}
		}				
	}

	a.f.cMenu.value = JSON.stringify(vaMenu) ;
}
</script>