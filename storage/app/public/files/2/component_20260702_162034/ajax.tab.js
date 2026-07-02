/*
Form Multi Tab
*/
const tab = {
  va:{},
	init(){
		if(typeof this.va.vaTab !== "undefined"){
			for(key in this.va.vaTab){
				this.va.fieldFocus[key] = null ;
			}
		}
	},
  createTab: function(divMain,va){		
		divMain.id = va.name ;
		divMain.innerHTML = "" ;
		divMain.style.display = "" ;

		this.va = va ;
		this.va.divMain = divMain ;

		this.va.divTab = a.addObj("div",this.va.divMain,"","tab_tab") ;				// Membuat Tab untuk Induk Tab_Cell
		this.va.divBody = a.addObj("div",this.va.divMain,null,"tab_body") ;		// Yang berisi Content Dir dari semua tab
		this.va.nHeightContent = this.va.divBody.offsetHeight ;
		this.va.divBody.style.maxHeight = this.va.nHeightContent ;
		this.va.divBody.style.maxWidth = this.va.divBody.clientWidth ;

		this.va.divCell = [] ;			// Div Tab Cell nya
		this.va.divContent = [] ;		// array daftar div yang akan di tampilkan
		this.va.fieldFocus = [] ;
		this.va.nOldTab = -1 ;
		this.va.currTab = 0 ;
		this.va.classClick = "tab_item tab_click no_txt_select" ;
		this.va.class = "tab_item tab_normal no_txt_select" ;
		let nTab = -1 ;
    for(value of this.va.vaTab){
			nTab ++ ;
      this.va.divCell[nTab] = a.addObj("div",this.va.divTab,null,this.va.class,null,value.title) ;
			this.va.fieldFocus[nTab] = null ;
			if(value.selected) this.va.currTab = nTab ;
			(function(tab,divCell,nTab){
				divCell.onclick = function(){
					if(!txt.canClick(divCell)) return false ;

					tab.Click(nTab,true) ;
				} ;
			})(this,this.va.divCell[nTab],nTab)

			var tabContent = a.getById(value.divcontent,this.va.divBody) ;			
			if(tabContent == null){
				tabContent = a.getById(value.divcontent) ;
				if(tabContent !== null){
					tabContent.className = "tab_content" ;
					this.va.divBody.appendChild(tabContent);
					this.va.divContent[nTab] = tabContent ;
					this.initComponent(tabContent,nTab) ;
				}
			}
    }

		this.Click(this.va.currTab) ;
  },
	lastFieldFocus(field){
		if(typeof field.sisTab !== "undefined"){
			let nTab = field.sisTab ;
			this.va.fieldFocus[nTab] = field ;
		}
	},
	initComponent(div,nTab){
		setTimeout((div,nTab)=>{
			const fields = div.querySelectorAll("input, button");
			for(field of fields){
				field.sisTab = nTab ;
			}	
		},0,div,nTab) ;
	},
	Click(nTab,lClick){this.click(nTab,lClick)},
	click(nTab,lClick){
		// Kalau Click Di tempat sama kita tidak proses bawahnya, cek juga jika oldtab undefined maka dipastikan diluar form / dari openform
		if(this.va.nOldTab == nTab || typeof this.va.nOldTab == "undefined") return true ;
		let div = this.va.divCell[nTab] ;
		if(this.va.nOldTab >= 0){
			let oldDiv = this.va.divCell[this.va.nOldTab] ;
			oldDiv.className = this.va.class ;

			//this.va.divContent[this.va.nOldTab].style.display = "none" ;
			this.va.divContent[this.va.nOldTab].style.visibility = "hidden" ;
		}
		div.className = this.va.classClick ;
		this.va.nOldTab = nTab ;

		let divContent = this.va.divContent[nTab] ;
		with(divContent.style){
			//position = "relative";
			//display = "block" ;
			visibility = "visible" ;
			top = 0 ;
			opacity = 1 ;
			height = this.va.nHeightContent - 8 ;
		}		

		// Jika Kita click Kita akan arahkan cursor ke field terakhir di tab itu sebelum beralih tab
		if(lClick && this.va.fieldFocus[nTab] !== null) fieldfocus(this.va.fieldFocus[nTab]) ;
	},
	currTab(){
		return this.va.nOldTab ;
	},
}