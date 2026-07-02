/*
Class Baru Untuk Pengganti DBGRID yang lama dia menggunakan Format yang lebih sederhanya yaitu hanya terdiri dari 2 object 
1. Div -> Box Grid ( divMain )
2. Table -> isi Grid ( tabMain )
	  div-Border
			div Caption
			div Body
		  	table
			  	thead
				  	tr
					  	td
					tbody
				  	tr
					  	td
			div Footer Main
				div Footer -> tfoot
					div cellFooter -> cell_footer

3. Event List
 		✅ DBGRID1_onAfterLoadArray()
 		✅ DBGRID1_onAfterUpdate(vaRow,nRow,nCol,value)
 		✅ DBGRID1_onBeforeEdit(vaRow,nRow,nCol)
 		✅ DBGRID1_onBeforeUpdate(vaRow,nRow,nCol,value)
 		✅ DBGRID1_onBottomScroll(nTop,nLeft)
 		✅ DBGRID1_onClick(vaRow,nCol)
 		✅ DBGRID1_onDblClick(vaRow,nCol)
 		✅ DBGRID1_onHeaderClick(nCol)
 		✅ DBGRID1_onTopScroll(nTop,nLeft) 
4. Methode List
		✅ DBGRID1.AppendRow(vaValue)
		✅ DBGRID1.CellPos(nRow,nCol)
		✅ DBGRID1.CellUpdate(nRow,nCol,value)
		✅ DBGRID1.CellValue(nRow,nCol)
		✅ DBGRID1.Cols()
		✅ DBGRID1.CurrCol()
		✅ DBGRID1.CurrRow()
		✅ DBGRID1.DeleteRow(nRow)
		✅ DBGRID1.DeleteRowAll()
		✅ DBGRID1.FooterUpdate(nCol,value)
		✅ DBGRID1.FooterValue(nCol)
		✅ DBGRID1.GridContent()
		✅ DBGRID1.HeaderUpdate(nCol,value)
		✅ DBGRID1.HeaderValue(nCol)
		✅ DBGRID1.InsertRow(nRow,vaValue)
		✅ DBGRID1.Rows()
		✅ DBGRID1.ScrollLeft(nLeft)
		✅ DBGRID1.ScrollTop(nTop)	
		✅ DBGRID1.AutoWidth()
5. Lain-lain
		✅ EditField
		✅ Kolom Resize
		✅ Menu Click Kanan
		Filter
		✅ Sort
		Find
		Print
		Export Excel
		Export PDF
		Hidden Colom
		Table Group
*/
class main_dbgrid {
	#cfg = {};

	get name(){return this.#cfg.conf.name ;}
	DeleteRowAll(){this.#cfg.tbody.innerHTML = "" ;}	// Hapus Semua Row
	Rows(){return this.#cfg.tbody.rows.length ;}			// Total Jumlah Record	
  Cols(){return this.#cfg.thead.rows[0].cells.length ;} // Jumlah Colom
	CurrRow(){return this.#cfg.tmp.nCurrRow};
  CurrCol(){return this.#cfg.tmp.nCurrCol};
	DeleteRow(nRow){if(this.#cfg.tbody.rows.length > nRow) this.#cfg.tbody.deleteRow(nRow) ;};

	AutoWidth(){
		let tr = this.#cfg.thead.rows[0] ;
		for(let cell of tr.cells){
			cell.style = "" ;
		}
	};

	CellPos(row,col){
		let cell = null ;
    if(typeof row == "number" && typeof col == "number"){
      row = Math.max(0,Math.min(row,this.Rows()-1)) ;
      col = Math.max(0,Math.min(col,this.Cols()-1)) ;
      cell = this.#cfg.tbody.rows[row].cells[col] ;

      this._cellClick(cell) ;
    }
		
    return [row,col,cell] ;
  };

	HeaderValue(nCol){
    cRetval = "" ;
    if(this.#cfg.thead.rows.length >= 1 && this.#cfg.thead.rows[0].cells.length >= nCol) cRetval = this.#cfg.thead.rows[0].cells[nCol].innerHTML ;
    return cRetval ;
  };

  HeaderUpdate(nCol,cValue){
    if(this.#cfg.thead.rows.length >= 1 && this.#cfg.thead.rows[0].cells.length >= nCol) this.#cfg.thead.rows[0].cells[nCol].innerHTML = cValue ;
  };

  FooterValue(nCol){
    let cRetval = null ;    
    if(this.#cfg.tfoot !== null){
			let div = this.#cfg.tfoot.children[nCol] ;
			if(typeof div !== "undefined" && div !== null) cRetval = div.innerHTML ;
    }
    return cRetval ;
  };

  FooterUpdate(nCol,cValue){
    if(this.#cfg.tfoot !== null){
			let div = this.#cfg.tfoot.children[nCol] ;
			if(typeof div !== "undefined" && div !== null) div.innerHTML = cValue ;
    }
  };

	constructor(conf,parent,lautoStart=false){
		this.#cfg = conf ;
		this.#cfg.tmp = {nCurrRow:0,nCurrCol:0} ;
		if(lautoStart) this._createGrid(parent) ;

		// Jika Data Ada Isi nya itu artinya di kirim data dari server langsung kita loadarray
		if(this.#cfg.data.length > 0) this.LoadArray(this.#cfg.data) ;
	};
	
	GridContent(){
		let va = {} ;
    let nRow = this.Rows() ;
    let nCol = this.Cols() ;
		for(let row=0;row<nRow;row++){
			let tr = this.#cfg.tbody.rows[row] ;
			va[row] = this._getRowData(tr,false) ;
		}
    return JSON.stringify(va) ;
  };
	
	_createGrid(parent){
		// Buat Div Main
		this.#cfg.divBorder = null ;
		if(typeof parent == "object" && parent.nodeName == "DIV"){
			this.#cfg.divBorder = parent ;
		}else{
			this.#cfg.divBorder = a.addObj("div",null,null,null,"width:100px;height:100px") ;
		}
		this.#cfg.divBorder.className = "dbg_container" ;
		this.#cfg.divBorder.textContent = "" ;
		this.#cfg.divBorder.style.display = "flex" ;
		if(typeof this.#cfg.conf.height !== "undefined"){
			this.#cfg.divBorder.style.height = this.#cfg.conf.height ;
		} 
		this.#cfg.divBorder.style.height = this.#cfg.divBorder.offsetHeight ;

		let divBorderWidth = this.#cfg.divBorder.offsetWidth ;
		this.#cfg.divBorder.style.width = divBorderWidth ;

		let capHeight = 0 ;
		if(this.#cfg.caption !== null && this.#cfg.caption !== ""){
			this.#cfg.divCaption = a.addObj("div",this.#cfg.divBorder,null,"dbg_caption2") ;
			this.#cfg.divCaption.innerHTML = this.#cfg.caption ;
			capHeight = this.#cfg.divCaption.offsetHeight ;
		}

		// Jika Toolbar = true kita buatkan Toolbar
		if(this.#cfg.conf.showToolbar == true){
			let divToolbar = a.addObj("div",this.#cfg.divBorder,null,"tbar_main","height:24px;margin-right:2px;margin-bottom:2px") ;
			
			/*tBar.add("Close","Exit Program","share/images/menu/menu-close.gif",function(){toolBar_Close()}) ;
			tBar.addSep() ;
			tBar.add("toolChange","Change Password","share/images/menu/menu-change-password.gif",function(){OpenForm('main.php?__par=./setup/changepassword.php','frmChangePassword','Change Password',500,200);}) ;
			tBar.addSep() ;
			tBar.add("changeThemes","Change Themes","share/images/menu/ch-themes.png",function(){MnuChangeThemes_onClick()}) ;
			tBar.show("ssTolbar1",null,null,24,null,divToolbar) ;*/
		}

		// Div untuk menampung Table
		this.#cfg.divMain = a.addObj("div",this.#cfg.divBorder,null,"dbg_main2") ;
		let divMain = this.#cfg.divMain ;
		divMain.style.height = this.#cfg.divBorder.offsetHeight - capHeight - 24 ;
		divMain.style.maxWidth = divMain.clientWidth ;
		divMain.id = "dbg_main_" + this.#cfg.conf.name ;
		if(this.#cfg.conf.scrolling.toLowerCase() == "vertical") divMain.style.overflowX = "hidden" ;
		if(this.#cfg.conf.scrolling.toLowerCase() == "horizontal") divMain.style.overflowY = "hidden" ;
		if(this.#cfg.conf.scrolling.toLowerCase() == "hidden") divMain.style.overflow = "hidden" ;
		
		(function(div,dbg){
			div.onscroll = function(){dbg.onScroll(div)};
		})(divMain,this)
		
		// Buat Button untuk mengunggu tombol di pencek biar bisa geser cel nya
		this.#cfg.button = a.addObj("button",window,null,null,"width:1px;height:1px;position:fixed;top:0px;left:0px;opacity:0") ;
		(function(button,dbg){
			button.onkeydown = function(){return dbg._btnKeyDown(event)}
		})(this.#cfg.button,this)

		// Buat Table main
		let tabMain = a.addObj("table",divMain,"dbg_tab_" + this.#cfg.conf.name,null,"border-spacing:0px;border-collapse: collapse;") ;
		tabMain.style.border = "1px" ;
		this.#cfg.tabMain = tabMain ;

		let thead = null ;
		let tbody = null ;
		let tr = null ;
		let td = null ;

		// Buat Header
		thead = a.addObj("thead",tabMain) ;//,"","","position: sticky;top: 0") ; //jika open form pada form tidak menumpuk cssnya pada form baru , dikembalikan karena sudah diset manual set colclass
		// Buat Body
		this.#cfg.tbody = a.addObj("tbody",tabMain) ;
		// Kalau ada showFooter maka kita buat footer
		let lfoot = false ;
		if(this.#cfg.conf.showFooter){
			lfoot = true ;
			let divFootMain = a.addObj("div",this.#cfg.divBorder,null,"dbg_footer_main2","position: sticky;top: 0") //jika open form pada form tidak menumpuk cssnya pada form baru //position: sticky;top: 0"
			this.#cfg.tfoot = a.addObj("div",divFootMain,"","dbg_footer2") ;

			divFootMain.style.width = divBorderWidth-2 ; // this.#cfg.divBorder.offsetWidth ; //-2 ;
		} 
		
		// Setting Top dan Footer
		tr = a.addObj("tr",thead) ;
		for(let col of this.#cfg.hIndex){
			 let value = this.#cfg.header[col] ;

			// Buat CSS Untuk Lebar Colom
			let clsName = this._setColClass(value,String2Number(value.width),true) ;

			// Membuat Cell Header
			td = a.addObj("th",tr,null,"dbg_cell_header2 dbg_cell_header2_custom no_txt_select " + clsName) ;
			if(value.type == "checkbox"){
				let img = a.addObj("img",td,null,null,"padding-top:1px") ;
				img.src = this.#cfg.conf.url + "/images/uncheck.gif" ;
				(function(img,dbg){
					img.onclick = function(){dbg._clickImg(img,true)} ;
					img.onmouseover = function(){dbg._checkOver(img,true);} ;
					img.onmouseout = function(){dbg._checkOver(img,false);} ;
				})(img,this)
			}else{
				td.innerHTML = value.caption !== null ? value.caption : col ;
			}
			(function(dbg,td){
				td.onclick = function(){dbg.headClick(td)}
			})(this,td)

			// Membuat Cell Footer
			if(lfoot){
				let css = typeof value.footeralign !== "undefined" ? ("text-align:" + value.footeralign) : "" ;
				let fText = typeof value.footertext !== "undefined" && value.footertext !== "" ? value.footertext : "&nbsp;" ;
				td = a.addObj("div",this.#cfg.tfoot,null,"dbg_cell_footer2 " + clsName,css,fText) ;
			}
		}
		this.#cfg.thead = thead ;
		
		// Buat Untuk Status Bar
		this.#cfg.SBar = a.addObj("div",this.#cfg.divBorder,null,"win_sbar") ;
		sBar.add("cell_1","",null,"left") ;
		sBar.add("cell_2","","10px","center") ;
		this.#cfg.SBar.data = sBar.show("FrmStatusBar",this.#cfg.SBar,"1px") ;
		this.#cfg.SBar.data["cell_2"].style.display = "none" ;

		this._initColResize() ;
	};

	StatusBar(cName,value){
		let divBar = this.#cfg.SBar ;
		if(divBar !== null && typeof divBar.data !== "undefined" && divBar.data !== null && typeof divBar.data[cName] !== "undefined"){
			divBar.data[cName].style.display = value !== "" ? "" : "none" ;
			divBar.data[cName].innerHTML = value ;
		}
	};
	
	_SBar_RowPos(){
		let nRow = Math.min(this.CurrRow()+1,this.Rows()) ;
		let cBar = "Row : " + nRow + " of " + this.Rows() ;
		this.StatusBar("cell_2",cBar) ;
	} ;
	
	_setColClass(head,value,lHeader=false){
		if(typeof head.cellIndex == "undefined") return null ;
	
		let clsName = "css_" + this.name + "_" + head.cellIndex ;
		let clsHeader = "css_header_" + this.name + "_" + head.cellIndex ; 
		let oStyle = a.addObjById(clsName,"style",document.head) ;
		let css = "width:"+value+"px; min-width:"+value+"px; max-width:"+value+"px;" ;
		if(typeof value == "string") css = value ;

		// Kita Cek apakah colom nya hidden apa tidak kalau hidden kita atur display nya
		const keys = Object.keys(this.#cfg.header);
		const cfg = this.#cfg.header[keys[head.cellIndex]] ;
		if(typeof cfg.display !== "undefined" && cfg.display == "hidden")	css = "display:none;" ;
		
		this.#cfg.header[keys[head.cellIndex]].css = css ;
		let cssHead = css + "position:sticky;top:0px;z-Index:0;" ; //awalnya zindex 2
		
		let cssContent = "." + clsHeader + " {" + cssHead + "} ." + clsName + " {" + css + "}";
		oStyle.textContent = cssContent ;
		
		if(lHeader) return clsHeader ;
		return clsName ;
	}
	
	_initColResize(withColFreeze=true){
		let o = this.#cfg.divMain ; // this.#cfg.thead ;
		let headers = this.#cfg.thead.firstElementChild ;
		let nTop = o.scrollTop ;
		let nLeft = 0 ;
		let nFreezeWidth = 0 ;
		for (const header of headers.cells) {
			if(this.#cfg.conf.colFreeze > header.cellIndex){
				nFreezeWidth += header.offsetWidth + 1 ;
				let clsName = "css_" + this.name + "_" + header.cellIndex ;
				let clsHeader = "css_header_" + this.name + "_" + header.cellIndex ;
				let oStyle = a.getById(clsName) ;
				if(oStyle !== null){
					const keys = Object.keys(this.#cfg.header);
 					let css = this.#cfg.header[keys[header.cellIndex]].css ;
					let cssHead = css + "position:sticky;top:0px;" ; //z-Index:4; diset ketika freeze
					if(withColFreeze){
						css = css + "position:sticky;left:" + nLeft + "px;z-Index:4;" ;
						cssHead += "left:" + nLeft + "px;z-Index:5;" ; //level head dinaikkan karena level 4 digunakan untuk data
					} 

					let cssContent = "." + clsHeader + " {" + cssHead + "} ." + clsName + " {" + css + "}";
					oStyle.textContent = cssContent ;
				}
			}

			nLeft += header.offsetWidth ;
			let id = "img_resize_" + this.name + "_" + header.cellIndex ;
			let div = a.getById(id) ;
			if(div == null){
				div = a.addObj("div",o,id) ;
				(function(dbg,div,header){
					div.onmousedown = function(){dbg._startColResize(event,div,header);} ;
					div.ondblclick = function(){dbg._mnu_autoWidth(header)};
				})(this,div,header)
			}
			div.style = "width:10px;cursor:col-resize;z-index:4;border:1px;top:" + nTop + "px;height:22px;display:block;position:absolute;left:" + (nLeft-5) + "px;" ;
		}

		// Jika colFreeze > 0 maka kita akan setting minimum nLeft dari cell, kita hitung dari sebelah kanan cell yang freeze terakhir
		let cellMinLeft = 0 ;
		if(this.#cfg.conf.colFreeze > 0){
			let cell = headers.cells[this.#cfg.conf.colFreeze-1] ;
			cellMinLeft = cell.offsetLeft + cell.offsetWidth ;

			if(typeof this.#cfg.divBackFreeze == "undefined" || typeof this.#cfg.divBackFreeze == null) this.#cfg.divBackFreeze = a.addObj("div",this.#cfg.divMain,null,"dbg_back_cellfreeze") ;
			let divBackFreeze = this.#cfg.divBackFreeze ;
			let rect = this.#cfg.divMain.getBoundingClientRect() ;
			let rectTab = this.#cfg.tabMain.getBoundingClientRect() ;
			divBackFreeze.style = "width:" + (nFreezeWidth-1) + ";top:" + rect.top + ";left:" + (rect.left+1) + ";height:" + (rect.height - 15) ;
		}
		this.#cfg.conf.cellMinLeft = cellMinLeft ;
	}	

	_mnu_autoWidth(header){		
		this._setColClass(header,"width:auto") ;
		this._setColClass(header,header.offsetWidth) ;
		this._initColResize() ;
	}

	_startColResize(event,div,header){
		let nCellWidth = header.offsetWidth ;
		let nLeft = div.offsetLeft ;
		let dbg = this ;
		let lInit = true ;
		a.obj_move_start(div,event,
      // Stop Event
      ()=>{
				lInit = true ;
				if(typeof dbg.#cfg.divBackFreeze !== "undefined") dbg.#cfg.divBackFreeze.style.background = "" ;
				dbg._initColResize() ;
      },
      // Move Event
      ()=>{
        let nMove = Math.max(10,nCellWidth + (div.offsetLeft - nLeft - 2)) ;
				dbg._setColClass(header,nMove) ;
				if(typeof dbg.#cfg.divBackFreeze !== "undefined") dbg.#cfg.divBackFreeze.style.background = "transparent" ;

				// Kalau Pertama di geser kita atur kolom tapi untuk yang colom freeze kita matikan dulu freeze nya
				if(lInit){					
					dbg._initColResize(false) ;
				} 
				lInit = false ;
			});
	}

	_GetCellContent = function(nRow,nCol,cTag){
    let oRetval = null ;
		let cell = typeof nRow == "object" ? nRow : null ;		// nrow Boleh diisi langsung cell biar lebih simple
		if(cell == null){
			if(this.#cfg.tbody.rows.length > nRow){
				if(this.#cfg.tbody.rows [nRow].cells.length > nCol){
					cell = this.#cfg.tbody.rows[nRow].cells[nCol] ;
				}
			}
		};

		if(cell !== null){
			if(cTag !== null && typeof cTag !== "undefined" && cTag.trim() !== "" && cTag == "img"){
				oRetval = cell.getElementsByTagName(cTag) ;
			}else{
				oRetval = cell ;
			}
		}
    return oRetval ;
  }

	cellUpdate(nRow,nCol,value){
		return this.CellUpdate(nRow,nCol,value) ;
	}
	
	CellUpdate(nRow,nCol,value){
    let img = this._GetCellContent(nRow,nCol,"img") ;
    if(img !== null && img.length > 0){
      if(img[0].src.indexOf("check.gif") >= 0){
        if(value == 1 || value == true){
          img[0].src = this.#cfg.conf.url + "/images/check.gif" ;
        }else{
          img[0].src = this.#cfg.conf.url + "/images/uncheck.gif" ;
        }
      }
    }else{
      let o = this._GetCellContent(nRow,nCol) ;
      if(o !== null) o.innerHTML = value ;
    }
	};

	_MaxScroll(){
    return (this.#cfg.divMain.scrollHeight - this.#cfg.divMain.clientHeight) ;
  };

	// Event pada saat pertamakali posisi scroll paling atas / 0.
	ScrollTop(nTop){
    if(typeof nTop == "number") this.#cfg.divMain.scrollTop = Math.min(nTop,this.MaxScroll()) ;
    return this.#cfg.divMain.scrollTop ;
  };
	
  ScrollLeft(nLeft){
    if(typeof nLeft == "number") this.#cfg.divMain.scrollLeft = nLeft ;
    return this.#cfg.divMain.scrollLeft ;
  };

  onScroll(div){
    let nTop = div.scrollTop ;
    let nLeft = div.scrollLeft ;
    let nMax = this._MaxScroll() ;		
		
		// Kita hanya setting Colresize kalau scroll sudah berhenti biar tidak terlalu banyak berubah.
		setTimeout(()=>{
			if(nTop == div.scrollTop){
				this._initColResize() ;
			}
		},500,nTop,div) ;

		if(typeof this.#cfg.tfoot !== "undefined" && this.#cfg.tfoot !== null) this.#cfg.tfoot.style.left = -nLeft ;

		if(this.#cfg.tmp.oldTop == null) this.#cfg.tmp.oldTop = -1 ;
		
		// Check Apabila scrollTop Pertamakali menyentuh angka = 0 maka jalankan event onTopScroll    
    if(nTop == 0 && this.#cfg.tmp.oldTop !== nTop) if(eval("typeof " + this.name + "_onTopScroll") == 'function') eval(this.name + "_onTopScroll")(0,nLeft) ;
    // Jika Pertama kali menyentuh Scroll Bottom jalankan Event onBottomScroll
    if(nTop == nMax && this.#cfg.tmp.oldTop !== nTop) if(eval("typeof " + this.name + "_onBottomScroll") == 'function') eval(this.name + "_onBottomScroll")(nTop,nLeft) ;
    this.#cfg.tmp.oldTop = nTop ;
  };

	headClick(cell){
		if(eval("typeof " + this.name + "_onHeaderClick") == 'function'){
      eval(this.name + "_onHeaderClick(" + cell.cellIndex + ");") ;
    }
	};

	_isVisible(cell) {
		if(typeof cell == "undefined" || cell == null) return false ;
		let lRetval = cell.offsetParent !== null ;		// Kalau Induknya ada yang hidden maka offsetparrent akan null
		return lRetval ;
	};

	// Event untuk menunggu Tombol yang tekan 
	_btnKeyDown(event){
		let cell = this.#cfg.tmp.oldClick ;
		let tr = cell.parentElement ;		// Ambil TR
		let r = this.CurrRow() ;
		let c = this.CurrCol() ;
		let clientHeight = this.#cfg.divMain.clientHeight ;			// Menghitung Client Height untuk tbody = divMain - thead - tfoot
		if(typeof this.#cfg.thead !== "undefined") clientHeight -= this.#cfg.thead.offsetHeight ;
		if(typeof this.#cfg.tfoot !== "undefined") clientHeight -= this.#cfg.tfoot.offsetHeight ;
		let rowPage = parseInt(clientHeight / cell.offsetHeight) ;		// Jumlah Baris dalam satu page 
		
		let key = event.keyCode ;
		let newTr = null ;
		let newCell = null ;
		if(key == 38){				// Panah Atas
			this.CellPos(--r,c) ;
		}else if(key == 39){	// Panah Kanan
			let lFound = false ;
			for(let nCol=c+1;nCol<this.Cols();nCol++){
				let va = this.CellPos(r,nCol) ;
				if(this._isVisible(va[2])){
					lFound = true ;
					break ;
				}
			}
			if(!lFound) this.CellPos(r,c) ;
		}else if(key == 40){	// Panah Bawah
			this.CellPos(++r,c) ;
		}else if(key == 37){	// Panah Kiri
			let lFound = false ;
			for(let nCol=c-1;nCol>=0;nCol--){
				let va = this.CellPos(r,nCol) ;
				if(this._isVisible(va[2])){
					lFound = true ;
					break ;
				}
			}
			if(!lFound) this.CellPos(r,c) ;
		}else if(key == 33){    // pgUp
      if(event.altKey){					// Kalau tekan tombol alt maka kita move ke element paling atas
				r = 0 ;
      }else{
        r -= rowPage ;
      }
			this.CellPos(r,c) ;
    }else if(key == 34){    // pgDown
      if(event.altKey){
        r = this.Rows() - 1 ;
      }else{
        r += rowPage ;
      }
			this.CellPos(r,c) ;
    }else if(key == 35){    // end
			r = this.Rows() - 1 ;
			c = this.Cols() -1 ;
			this.CellPos(r,c) ;
	  }else if(key == 36){    // Home
			this.CellPos(0,0) ;
		}else if (key == 13){
			this._cellClick(cell,true) ;
		}
	};

	cellValue(nRow,nCol){return this.CellValue(nRow,nCol);};
	CellValue(nRow,nCol){
		let cell = typeof nRow == "object" ? nRow : null ;
		if(cell == null){
			if(this.#cfg.tbody.rows.length > nRow){
				let tr = this.#cfg.tbody.rows[nRow] ;
				if(tr.cells.length > nCol){
					cell = tr.cells[nCol] ;
				}
			}
		}
		
		let cRetval = null ;
		if(cell !== null){
			let colName = this.#cfg.hIndex[cell.cellIndex] ;
			let type = this.#cfg.header[colName].type ;
			if(type !== "undefined"){
				if(type == "checkbox"){
					cRetval = 0 ;
					let img = this._GetCellContent(cell,0,"img") ;
					if(img !== null && img.length > 0){
						if(img[0].src.indexOf("check.gif") >= 0) cRetval = this._isChecked(img[0]) ;
					}
				}else{
					cRetval = cell.textContent ;
				}
			}
		}
		return cRetval ;
	};

	// Untuk Mengambil Data pada Row itu berisi Object 
	// Object key adalah nomor urut Kolom dan juga Nama kolom untuk compatible dengan system lama 
	// Masih menggunakan Nomor kolom
	_getRowData(row,lWithColNumber=true){
		let vaRow = {} ;
		let nCol = 0 ;
		
		for(const cell of row.cells){
			let colName = this.#cfg.hIndex[cell.cellIndex] ;
			let cellValue = this.CellValue(cell) ;
			if(lWithColNumber) vaRow[nCol++] = cellValue ;
			vaRow[colName] = cellValue ;
		}
		return vaRow ;
	};
	
	_cellClick(cell,ldblClick=false){
		let tr = cell.parentElement ;		// Ambil TR

		// Kita Akan Menyimpan cell ke dalam oldClick untuk class name nya biar nanti kalau kita click cell lain
		// cell lama classname akan kita kembalikan
		if(typeof cell.defaultClass == "undefined") cell.defaultClass = cell.className ;
		if(typeof this.#cfg.tmp.oldClick !== "undefined"){
			this.#cfg.tmp.oldClick.className = this.#cfg.tmp.oldClick.defaultClass ;
		} 
		cell.className += " dbg_cell_body_click2";
		this.#cfg.tmp.oldClick = cell ;
		
		this._setScroll(cell) ;
		fieldfocus(this.#cfg.button) ;
		
		// Menyimpan Posisi Row Dan Col
		let nHeaderRows = this.#cfg.thead.rows.length ;
		this.#cfg.tmp.nCurrRow = tr.rowIndex - nHeaderRows ;
		this.#cfg.tmp.nCurrCol = cell.cellIndex ;

		let vaRow = this._getRowData(tr) ;
		if(ldblClick){
			// Kita lihat kalau dia status editfield = true maka kita akan edit field nya
			let f = this.#cfg.header[this.#cfg.hIndex[cell.cellIndex]] ;
			if(typeof f.edit !== "undefined" && f.edit){
				let lValidEdit = true ;
				if(eval("typeof " + this.name + "_onBeforeEdit") == 'function') lValidEdit = eval(this.name + "_onBeforeEdit")(vaRow,this.#cfg.tmp.nCurrRow,this.#cfg.tmp.nCurrCol) ;
				if(lValidEdit){
					// Kalau posisi Edit
					this.#cfg.tmp.cellEdit = {"html":cell.innerHTML,"className":cell.className,"value":cell.textContent,"style":cell.style.cssText,"vaRow":vaRow} ;			// Value Cell lama kita simpan
					cell.innerHTML = "" ;
					let field = a.addObj("input",cell,"txt-dbg_" + cell.cellIndex,null,cell.style.cssText + ";width:100%;border:0px;outline: none;height:"+(cell.clientHeight-1) + ";width:100%;border:0px;outline: none;height:"+(cell.clientHeight-1)) ;
					field.value = this.#cfg.tmp.cellEdit.value ;
					field.keyDown = 0 ;
					field.keyPress = 0 ;
					fieldfocus(field,f.type) ;
					(function(field,dbg,cell,f){
						field.onblur = function(){dbg._editOnBlur(field,cell)};
						field.onkeydown = function(event){
							field.keyDown = event.keyCode ;
							field.keyPress = 0 ;
							dbg._editKeyDown(event,field,cell) ;
						} ;
						field.onkeypress = (event)=>{
							field.keyPress = event.keyCode;
						}
						if(f.type == "number"){
							field.oninput = ()=>{txt.numFormat(field,0,field.keyDown,field.keyPress)} ;
						}
					})(field,this,cell,f)
					cell.style= "padding:0px;background-color:#ffffff" ;
				}
			}

			if(eval("typeof " + this.name + "_onDblClick") == 'function'){
				eval(this.name + "_onDblClick")(vaRow,cell.cellIndex) ;
			} 
		}else{
			// Kita Check kalau click 2x dengan waktu 500ms kita tidan anggap click 2x
			if(txt.canClick(cell,500)){
				if(eval("typeof " + this.name + "_onClick") == 'function'){
					eval(this.name + "_onClick")(vaRow,cell.cellIndex);
				}
			}
		}	
		
		this._SBar_RowPos() ;
	};

	_editKeyDown(event,field,cell){
		let row = this.CurrRow() ;
		let col = this.CurrCol() ;
		let lMove = false ;
		if(event.keyCode == 13){
			row ++ ;
			lMove = true ;
		}
		if(lMove){
			this.CellPos(row,col) ;
		} 
	}

	_editOnBlur(field,cell){
		cell.innerHTML = this.#cfg.tmp.cellEdit.html ;
		if(this._validEdit(field)){
			cell.textContent = field.value ;
			if(eval("typeof " + this.name + "_onAfterUpdate") == 'function') eval(this.name + "_onAfterUpdate")(this.#cfg.tmp.cellEdit.vaRow,this.CurrRow(),this.CurrCol(),field.value) ;
		}
		cell.style = this.#cfg.tmp.cellEdit.style ;
		a.delObj(field) ;
	}
	
	_validEdit(field){
		let vaRow = this.#cfg.tmp.cellEdit.vaRow ;
		let nRow = this.CurrRow() ;
		let nCol = this.CurrCol() ;
		let value = field.value ;
		let cEvent = this.name + "_onBeforeUpdate" ;
		let lRetval = true ;
		if(eval("typeof " + cEvent) == 'function'){
			lRetval = eval(cEvent)(vaRow,nRow,nCol,value);
		}
		return lRetval ;
	}

	// Mengatur posisi Scroll menghitung posisi cell.
	_setScroll(cell){
		let firstTr = this.#cfg.tbody.firstElementChild.offsetTop ;
		let cellTop = cell.offsetTop ;
		let cellHeight = cell.offsetHeight ;
		let cellLeft = cell.offsetLeft ;
		let cellWidth = cell.offsetWidth ;

		let divHeight = this.#cfg.divMain.clientHeight ;
		let divWidth = this.#cfg.divMain.clientWidth ;
		let divScrollTop = this.#cfg.divMain.scrollTop ;
		let divScrollLeft = this.#cfg.divMain.scrollLeft ;
		
		// Menghitung tinggi footer
		let footHeight = this.#cfg.conf.showFooter ? this.#cfg.tfoot.firstElementChild.offsetHeight : 0 ;
		
		// Kita Akan hitung posisi Scroll Top nya
		if(cellTop + cellHeight + footHeight > divHeight + divScrollTop){
			this.#cfg.divMain.scrollTop = cellTop + cellHeight + footHeight - divHeight ;
		}
		if(divScrollTop > cellTop - firstTr){
			this.#cfg.divMain.scrollTop = Math.max(0,cellTop - firstTr) ;
		}

		// Minimum Batas Kiri kalau ada colFreeze maka total lebar colFreeze		
		let cellMinLeft = this.#cfg.conf.cellMinLeft ;
		if(cellLeft < cellMinLeft){
			let nLeft = Math.max(0,divScrollLeft - (cellMinLeft - cellLeft)) ;
			this.#cfg.divMain.scrollLeft = nLeft ;
			
			divScrollLeft = this.#cfg.divMain.scrollLeft ;
			cellLeft = cell.offsetLeft ;
		}

		// Kita Hitung Scroll Leftnya
		if(cellLeft + cellWidth > divWidth + divScrollLeft){
			this.#cfg.divMain.scrollLeft = cellLeft + cellWidth - divWidth ;
		}
		if(divScrollLeft > cellLeft){
			this.#cfg.divMain.scrollLeft = Math.max(0,cellLeft) ;
		}
	};

	_isChecked = function(img){
    let lRetval = 1 ;
    if(img.src.indexOf("uncheck.gif") >= 0) lRetval = 0 ;
    return lRetval ;
  };

	_clickImg(img,lAll=false){
		if(!txt.canClick(img,500)) return false ;
		
    let c = "uncheck.gif" ;
		if(!this._isChecked(img)) c = "check.gif" ;
		img.src = this.#cfg.conf.url + "/images/" + c ;
		
		// Jika lAll Berarti kita click header nya maka semua akan kita sama kan valuenya
		if(lAll){
			let col = img.parentElement.cellIndex ;
			for(const row of this.#cfg.tbody.rows){
				let cell = row.cells[col] ;
				let _img = this._GetCellContent(cell,null,"img") ;
				if(_img !== null && _img.length > 0){
					_img[0].src = img.src ;
				}				
			}
		}	
	};

	_checkOver(img,lOver){
		let c = "uncheck.gif" ;
    let c1 = lOver ? "over-" : "" ;
    if(this._isChecked(img)) c = "check.gif" ;  
    img.src = this.#cfg.conf.url + "/images/" + c1 + c;
	}

	InsertRow(nRow,vaValue,lcolResize=true){
		// Kalau vaValue Object tapi bukan array maka kita convert ke Array
		if(typeof vaValue == "object" && !Array.isArray(vaValue)){
			vaValue = Object.values(vaValue) ;
		}
		
		// vaValue jumlah kolom kita samakan dengan table kalau kurang akan kita tambah biar sama
		if(vaValue.length < this.#cfg.hIndex.length){
			let n = this.#cfg.hIndex.length - vaValue.length ;
			for(let x=0;x<n;x++){
				vaValue.push("") ;
			}
		}

		nRow = Math.min(nRow,this.#cfg.tbody.rows.length) ;
		let vaHeader = this.#cfg.header ;
		let tr = this.#cfg.tbody.insertRow(nRow) ;
		let i = 0 ;
		let _nColIndex = 0 ;
		for(const col in vaValue){
			let clsName = "css_" + this.name + "_" + _nColIndex ;		// Nama Class Untuk mengatur Lebar Colom
			let colHeader = this.#cfg.hIndex[_nColIndex++] ;
			if(typeof colHeader !== "undefined"){
				let css = "" ;
				if(typeof vaHeader[colHeader] !== "undefined") css = "text-align:"+vaHeader[colHeader].align ; 			
				let td = a.addObj("td",tr,null,"dbg_cell_body2 no_txt_select " + clsName,css) ;
				if(typeof vaHeader[colHeader] !== "undefined" && vaHeader[colHeader].type == "checkbox"){
					let src = vaValue[col] == 1 || vaValue[col] == true ? "check.gif" : "uncheck.gif" ;
					src = this.#cfg.conf.url + "/images/" + src ;
					let img = a.addObj("img",td,null,null,"padding-top:1px") ;
					img.src = src ;
					(function(img,dbg){
						img.onclick = function(){dbg._clickImg(img)} ;
						img.onmouseover = function(){dbg._checkOver(img,true);} ;
						img.onmouseout = function(){dbg._checkOver(img,false);} ;
					})(img,this)
				}else{
					td.innerHTML = vaValue[col] ;
				}				
				(function(td,dbg){
					td.onclick = function(){dbg._cellClick(td)} ;
					td.ondblclick = function(){dbg._cellClick(td,true)} ;
					td.oncontextmenu = function(event){return dbg._conMenu(event,td);};
				})(td,this)
			}			
		}
		if(lcolResize) this._initColResize() ;
	};

	/*
	LoadArray kita buat unsyncronus biar tidak nunggu lama
	*/
	LoadArray(cJSON){
		let nTime = Math.max(10,(a.Response.end - a.Response.start))/1000 ;
		this.StatusBar("cell_1","Load grid in " + nTime.toFixed(3) + " sec") ;
		setTimeout((cJSON,dbg)=>{
			let obj = a.str2JSON(cJSON) ;
			dbg.DeleteRowAll() ;
			while(!obj.eof){
				let nRow = dbg.#cfg.tbody.rows.length ;
				dbg.InsertRow(nRow,obj.getRow,false) ;
				obj.moveNext ;
			}
			dbg._initColResize() ;
			dbg._SBar_RowPos() ;
		},0,cJSON,this) ;
	};

	AppendRow(vaRow){
		let nRow = this.#cfg.tbody.rows.length ;
		this.InsertRow(nRow,vaRow) ;
	}	

	/*
	Untuk Menu Kalau di Click Kanan
	*/
	_conMenu(e,cell){
    let bg = a.addBack("RighMenu-Back",null,0) ;
    bg.id = "RighMenu-Back" ;
    (function(dbg,bg){
      bg.onclick = function(){dbg._mnuClose();} ;
    })(this,bg);

    let vaMouse = a.getCursor(e) ;
		let css = "top:" + vaMouse [1] + ";left:" + vaMouse [0] + ";width:auto;height:auto;display:block" ;
    let mb = a.addObj("div",null,"GRID-Contect-Menu","dbg_mnu_context no_txt_select",css) ;    
    setObjIndex(mb) ;
    let mn = a.addObj("table",mb) ;
    mn.border = 0 ;
    mn.cellspacing = 0 ;
    mn.cellpadding = 0 ;
  
    let dbg = this ;
    this._addMenuItem("Sort Ascending",mn,function(){dbg._mnuSort(0,cell);}) ;
		this._addMenuItem("Sort Descending",mn,function(){dbg._mnuSort(1,cell);}) ;
		this._addMenuItem("-",mn,null) ;
		this._addMenuItem("Copy",mn,function(){dbg._clickMenu(1,cell);}) ;
    this._addMenuItem("Copy HTML Format",mn,function(){dbg._clickMenu(2,cell);}) ;
    
    return false ;
  };

	_mnuSort(par,cell){
		setTimeout((par,cell)=>{
			this._mnuClose() ;
			let rows = [] ;
			let row = 0 ;
			let col = cell.cellIndex ;
			for(const value of this.#cfg.tbody.rows){
				rows[row++] = value ;
			}
			rows.sort((a, b) => {
				let aValue = a.cells[col].textContent ;
				let bValue = b.cells[col].textContent ;
				if(par == 1){
					let tmp = aValue ;
					aValue = bValue ;
					bValue = tmp ;
				}

				return aValue.localeCompare(bValue, undefined, { numeric: true, sensitivity: 'base' });
			});
			// Updating the table body with the sorted rows
			rows.forEach(row => this.#cfg.tbody.appendChild(row));
		},0,par,cell) ;
	}

	_clickMenu(nMenu,cell){
    let txt = (nMenu == 1) ? cell.textContent : cell.innerHTML ;
    this._copyToClipboard(txt) ;
    this._mnuClose() ;
  };

  _addMenuItem(title,parent,callBack){
		let clsName = title == "-" ? "" : "dbg_mnu_context_item no_txt_select" ;
		let cStyle = title == "-" ? "height:5px;max-height:5px" : "" ;
		let tr = a.addObj("tr",parent) ;
		title = title == "-" ? "<hr style='border-top: 1px solid #dddddd;'>" : title ;
		let item = a.addObj("td",tr,null,clsName,cStyle) ;
		item.innerHTML = title ;

		if(callBack !== null) item.onclick = callBack ;
  };

  _mnuClose(){
    a.delById("RighMenu-Back") ;
    a.delById("GRID-Contect-Menu") ;
  };

  _copyToClipboard(text) {    
    let css = "position:fixed;top:0;left:0;width:'2em';height:'2em';padding:0;border:'none';outline:'none';boxShadow:'none';background:'transparent';opacity:0" ;
    let txt = a.addObj("textarea",null,null,null,css);
		
    txt.value = text;
    txt.select();
    try {
      document.execCommand('copy');
    } catch (err) {}
    a.delObj(txt) ;
  }
}