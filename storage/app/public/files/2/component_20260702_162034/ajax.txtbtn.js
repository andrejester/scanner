const txtBtn = {
	back:null,mainWin:null,isClose:true,obj:null,mainTab:null,filterInput:null,filterLabel:null,trHeader:null,tBody:null,
	vaRows:[],vaMsg:{field:null,callBack:null},
	Browse(method,field,callBack,parameter,lNew=false,lSkipEmpty=true,url=""){
		if(typeof method == "undefined" || method == "") method = field.name + "_Browse" ;
		// Kalau dia this.value == "" dan lskipempty=true dan bukan button click dan callback == object maka valueketerangan kita kosongkan juga
		if(!txt.data[field.id].input.buttonClick && lSkipEmpty && field.value == "" && typeof callBack == "object"){
			Object.keys(callBack).forEach(key => {
				callBack[key].value = "" ;
			})
		}else{
			// Kalau va.Browse = object dan va.browse.value == field.value tidak usah di browse ulang
			// langsung pakai data vaBrowse biar lebih mudah.
			if(typeof txt.data[field.id].browse.vaRow == "object" && txt.data[field.id].browse.value == field.value && !txt.data[field.id].input.buttonClick){
				txtBtn.PickField(txt.data[field.id].browse.vaRow,field,callBack) ;
			}else{
				let div = a.wait(10,"Tunggu Sebentar") ;
				div.style.opacity = 0 ;
				div.style.cursor = "default" ;
				txtBtn.Open(method,field,callBack,parameter,lNew,url) ;
			}
		}
	},
	getKeyValue(vaRow){
		lFirst = true ;
		var va = {"key":"","vaRow":{}} ;
		var nCol = 0 ;
		for (const col in vaRow) {
			// Kolom Pertama adalah Key
			if(lFirst){
				va["key"] = vaRow[col] ;
				lFirst = false ;
			}
			va["vaRow"][col] = vaRow[col] ;
			va["vaRow"][nCol] = vaRow[col] ;
			nCol ++ ;
		}
		return va ;
	},
	PickField: function(vaRow,field,callBack){
		var va = txtBtn.getKeyValue(vaRow) ;
		txt.data[field.id].browse.count = 0 ;		// Counter Kita Reset
    field.value = va.key ;
    txtBtn.closeTable(field) ;
    if(callBack){
			if(typeof callBack == "function"){
				callBack(va.vaRow) ;
			}else if(typeof callBack == "object"){			// Kalau Jenis Object Maka kita langsung ini value nya {"Keterangan":fieldKeterangan}
				Object.keys(callBack).forEach(key => {
					callBack[key].value = vaRow[key] ;
				})
			}			
      if(txt.data[field.id].input.buttonClick){
				fieldfocus(field) ;
			}
    }else{
      fieldfocus(field) ;
    }
  },
	Open(method,field,callBack,parameter,lNew=false,url=""){
		txtBtn.vaMsg.field = field ;
		txtBtn.vaMsg.callBack = callBack ;
		
		if(typeof parameter == "undefined" || parameter == "") parameter = field.name + "=" + field.value ;
		if(!svr.IsMVC()){
			// Dia di panggil dari field.browse
			if(lNew){
				method += "()" ;
			}else{
				url = __COMPONENT_FOLDER__ + "/ajax.ajax.php" ;
		  	parameter = "cSQL=" + method + "&cFieldName=" + field.name ;
				method = "_Browse()" ;
			}
		}

		a.ajax(url,method,parameter,function(obj){
			a.endwait() ;
			if(obj.dataRows == 0){
				// Kita akan cek kalau data tidak di temukan sebanyak 3x di ulang, dan value tidak dirubah maka akan kita kosongkan
				if(typeof txt.data[field.id].found == "undefined" || txt.data[field.id].found.value !== field.value){
					txt.data[field.id].found = {"value":field.value,"count":1} ;
				}else{
					txt.data[field.id].found.count ++ ;
				}
				let cmsg = "Data Tidak Ditemukan ....." ;
				if(txt.data[field.id].found.count > 3){
					cmsg = "Pengulangan Tidak boleh lebih dari 3x, field akan di kosongkan ...." ;
					field.value = "" ;
					txt.data[field.id].found = {"value":"","count":0} ;
				}
				txt.showTip(field,cmsg) ;
				fieldfocus(field) ;
			}else{
				if(obj.dataRows == 1){
					txtBtn.PickField(obj.getRow,field,callBack) ;
				}else if(obj.dataRows >= 1){
					let vaPos = field.getBoundingClientRect() ;
					frm.sendMessage({data:obj.data,vaPos:vaPos},"mainFrame","txtBtn.openRemoteTable",txtBtn.pickRemote,null) ;
				}else{
					txt.showTip(field,"Data Tidak Ditemukan ......") ;
					fieldfocus(field) ;
				}
			}
		}) ;
	},_rowSelected:"",
	openRemoteTable(obj){			
		let vaPos = obj.data.vaPos ;
		obj.data = obj.data.data ;

		txtBtn.isClose = false ;
		txtBtn.obj = obj ;
		// Buat Background
		txtBtn.back = a.addBack("_grid_background_",null,0) ;
		(function(txtBtn){
			txtBtn.back.onclick = function(){txtBtn.closeTable()} ;
		})(txtBtn) ;

		// Buat Box Untuk Grid	
		vaPos = txt.globalPos(vaPos) ;
		let nTop = vaPos.bottom + 1 ;
		let nLeft = vaPos.left ;
		var nWidth = 200 ;
		var nHeight = 260 ;
		var nMinHeight = 200 ;

		let css = "top:" + nTop + "px;left:" + nLeft + "px;width:" + nWidth + "px;height:" + nHeight + "px" ;
		txtBtn.mainWin = a.addObjById("_grid_main_win_","div",null,"browse_main",css) ;
		
		txtBtn.mainTab = a.addObj("table",txtBtn.mainWin,null,null,"border-spacing:0px;border-collapse: collapse;") ;

		txtBtn.CreateRemoteTable() ;

		// Atur Ukuran mainWin dengan standart 
		nWidth = Math.min(Math.max(txtBtn.mainTab.offsetWidth+18,200),400) ;
		nHeight = Math.min(Math.max(txtBtn.mainTab.offsetHeight,nMinHeight),400) ;
		txtBtn.mainWin.style.height = nHeight ;
		txtBtn.mainWin.style.width = nWidth ;

		// Kita akan atur lebar table kita samakan dengan lebar div biar tidak jelek jika table lebih kecil dari div
		if(txtBtn.mainTab.offsetWidth < nWidth){
			txtBtn.mainTab.style.width = nWidth ;
			txtBtn.mainWin.style.overflowX = 'hidden';
		}

		// Atur Lebar Input Search
		if(txtBtn.filterInput !== null) txtBtn.filterInput.style.width = nWidth - 80 ;

		// Kita setting Lebar kolom menjadi fixed yang sebelumnya otomatis mengikuti content
		// sehingga kalau di filter lebar kolom tidak berubah
		for(var col of txtBtn.trHeader.cells){
			col.style.width = col.offsetWidth ;
		}

		// Kalau kita hitung top + tinggi object melebih besar screen akan kita kecilkan tinggi nya tapi kalau lebih kecil dari tinggi
		// minimum maka akan kita buka ke atas
		let div = txtBtn.mainWin ;
		nHeight = div.offsetHeight ;
		if(nTop + nHeight + 15 > vaPos.scr.bottom){
			// Kita Hitung Ukuran Tinggi jika kita kurangi dengan Tinggi Screen
			let nNewHeight = nHeight - (nTop + nHeight - vaPos.scr.bottom) - 15 ;
			if(nNewHeight >= nMinHeight){
				// Kalau Tinggi Masih lebih tinggi dari minimal tinggi browse yaitu 200px akan kita atur ukurannya
				nHeight = nNewHeight ;
			}else{
				// Jika terlalu kecil hasil newHeight nya maka browse akan kita arahkan ke atas.
				nTop = vaPos.top - nHeight - 4 ;
			}
		}
		
		// Jika Batas Kanan Lebih Besar Dari Screen Kita Geser Kekiri
		if(div.offsetLeft + div.offsetWidth + 15 > vaPos.scr.right){
			let nMove = div.offsetLeft + div.offsetWidth - vaPos.scr.right + 15
			nLeft = nLeft - nMove ;
		}
		div.style.left = nLeft ;
		div.style.height = nHeight ;
		div.style.top = nTop ;

		// Atur Posisi Index Object
		txtBtn.objIndex() ;

		// Field Focus Search
		if(txtBtn.filterInput !== null) fieldfocus(txtBtn.filterInput) ;
	},
	CreateRemoteTable(){
		let data = this.obj.data ;
		
		var thead = a.addObj("thead",txtBtn.mainTab,null,null,"position: sticky;top: 0") ;
		txtBtn.tBody = a.addObj("tbody",txtBtn.mainTab,"browseBody") ;
		txtBtn._rowSelected = "" ;

		// Kalau Row Lebih dari 50 maka kita akan buatkan Field Pencarian
		var trSearch = a.addObj("tr",thead) ;

		var lFirst = true ;
		var nColCount = 0 ;
		
		txtBtn.vaRows = [] ;
		let nRow = 0 ;
		for(const key in data){
			// Jika Baris Pertama maka buat tr untuk Heder nya.
			if(lFirst) txtBtn.trHeader = a.addObj("tr",thead,"_table_col_header_") ; //,null,"cursor:default;border-bottom:1px solid #bbbbbb") ;
			
			var tr = a.addObj("tr",txtBtn.tBody,"row_" + (nRow++),null,"cursor:default") ;
			tr.obj = data[key] ;
			// Menambah Event Dalam TR.
			(function(tr,vaRow,txtBtn){
				tr.onclick = function(){
					frm.responseMessage(vaRow,txtBtn.obj["par"]["caller"],txtBtn.obj["par"]["call_id"]) ;
					txtBtn.closeTable() ;
				}
			})(tr,data[key],txtBtn) ;
			
			for (const col in data[key]) {
				// Buat Kolom Untuk Header itu
				if(lFirst){
					var th = a.addObj("th",txtBtn.trHeader,null,"dbg_cell_header2 no_txt_select",null,col) ;
					nColCount ++ ;
				}
				var td = a.addObj("td",tr,null,"dbg_cell_body2 no_txt_select",null,data[key][col]) ;
			}
			lFirst = false ;
			txtBtn.vaRows.push(tr) ;
		}
		
		txtBtn.filterInput = null ;
		if(trSearch !== null){
			var td = a.addObj("td",trSearch,null,null,"padding:4px 4px 4px 4px;background-color:var(--bodyBackColor)") ;
			td.colSpan = nColCount ;
			txtBtn.filterLabel = a.addObj("label",td) ;
			
			var span = a.addObj("span",txtBtn.filterLabel,null,null,"vertical-align: middle;padding-top:2px","Filter : ") ;
			txtBtn.filterInput = a.addObj("input",txtBtn.filterLabel,null,null,"height:22px") ;
			txtBtn.filterInput.placeholder = "🔍 Filter" ;
			(function(input){
				input.onkeypress = function(event){
					if(event.keyCode == 13){
						if(txtBtn._rowSelected !== ""){
							frm.responseMessage(txtBtn._rowSelected.obj,txtBtn.obj["par"]["caller"],txtBtn.obj["par"]["call_id"]) ;
							txtBtn.closeTable() ;
						}
					} ;
				}
				input.onkeydown = function(event){
					input.oldValue = input.value ;
					if(event.keyCode == 38){
						txtBtn.moveRow(-1) ;
					}else if(event.keyCode == 40){
						txtBtn.moveRow(1) ;
					}
				}
				input.onkeyup = function(event){
					if(input.oldValue !== input.value){
						txtBtn.filterData(input) ;
					}
				}
			})(txtBtn.filterInput)
		}	
	},
	pickRemote(vaRow){
		let field = txtBtn.vaMsg.field ;
		let callBack = txtBtn.vaMsg.callBack ;

		txtBtn.PickField(vaRow,field,callBack) ;
		txt.data[field.id].browse.count = 0 ;
		if(typeof txt.data[field.id].browse.vaRow !== "object"){
			setTimeout(txtBtn.browseTimeout,1000,field) ;
		}		
		txt.data[field.id].browse.vaRow = vaRow ;
		txt.data[field.id].browse.value = field.value ;

		fieldfocus(field) ;
		txtBtn.closeTable() ;
	},
	rowSelect(row,lOver){
		if(txtBtn._rowSelected !== "" && txtBtn._rowSelected !== null){
			txtBtn._rowSelected.className = "dbg_cell_body2" ;
			//txtBtn._rowSelected.style.backgroundColor = "#ffffff" ;
		}
		txtBtn._rowSelected = "" ;
		if(lOver){
			txtBtn._rowSelected = row ;
			//txtBtn._rowSelected.className = "dbg_cell_body_click2" ; //.style.backgroundColor = "#dedede" ; diremark agar tulisan terlihat ketika find / filter dropdown
		}
	},
	moveRow(nRow){
		var vaRow = {"prev":null,"curr":null,"next":null} ;
		var lFound = false ;
		for(n=0;n<txtBtn.vaRows.length;n++){
			var row = txtBtn.vaRows[n] ;
			if(row.style.display == ""){
				vaRow.prev = vaRow.curr ;
				vaRow.curr = vaRow.next ;
				vaRow.next = row ;
			}
			if(txtBtn._rowSelected == ""){
				txtBtn.rowSelect(row,true) ;
				lFound = true ;
				break;
			}else if(vaRow.curr !== null && vaRow.curr.id == txtBtn._rowSelected.id){
				row = nRow == -1 ? vaRow.prev : vaRow.next ;

				if(row !== null) txtBtn.rowSelect(row,true) ;
				lFound = true ;
				break;
			}
		}
		
		// Kalau dia panah ke atas dan posisi Row adalah paling bawah maka kita naikkan 1
		if(!lFound && nRow == -1 && vaRow.next !== null && vaRow.curr !== null && vaRow.next.id == txtBtn._rowSelected.id){
			txtBtn.rowSelect(vaRow.curr,true) ;
		}
		
		if(txtBtn._rowSelected !== "" && txtBtn._rowSelected !== null){
			var cellTop = txtBtn._rowSelected.offsetTop ;
			var cellHeight = txtBtn._rowSelected.offsetHeight ;
			var divHeight = txtBtn.mainWin.clientHeight ;
			var divScroll = txtBtn.mainWin.scrollTop ;
			var tHeaderHeight = txtBtn.trHeader.offsetHeight ;
			if(cellTop + cellHeight > divHeight + divScroll){
				txtBtn.mainWin.scrollTop = cellTop + cellHeight - divHeight ;
			}
			if(divScroll > cellTop - 55){
				txtBtn.mainWin.scrollTop = Math.max(0,cellTop - 55) ;
			}
		}
		
	},
	filterData(field){
		let body = a.getById("browseBody",txtBtn.mainTab) ;
		let firstFoundRow = null ;
		txtBtn.vaRows = [] ;
		for(const row of body.rows){
			var lFound = false ;
			for(const cell of row.cells){
				var text = cell.textContent ;
				if(text.toLowerCase().includes(field.value.toLowerCase())){
					if(firstFoundRow == null) firstFoundRow = row ;
					lFound = true ;
				}
			}
			if(lFound){
				txtBtn.vaRows.push(row) ;
			}
			row.style.display = lFound ? "" : "none" ;
			
			// Row Pertama akan di sorot
			if(firstFoundRow !== null) txtBtn.rowSelect(firstFoundRow,true) ;
		}
	},
	browseTimeout: function(field){
		txt.data[field.id].browse.count ++ ;
		if(txt.data[field.id].browse.count < 15){
			setTimeout(txtBtn.browseTimeout,1000,field) ;
		}else{
			txt.data[field.id].browse.count = 0 ;
			txt.data[field.id].browse.vaRow = "" ;
			txt.data[field.id].browse.value = "" ;
		}
	},
	closeTable(){
		a.endwait() ;
		txtBtn.isClose = true ;
		a.delObj([txtBtn.mainWin,txtBtn.back]) ;
	},objIndex_Count:-1,
	objIndex(){
		if(txtBtn.objIndex_Count < a.setObjIndex()){
			a.setObjIndex(txtBtn.back) ;
			txtBtn.objIndex_Count = a.setObjIndex(txtBtn.mainWin) ;
		}
		if(!txtBtn.isClose) setTimeout(txtBtn.objIndex,300) ;
	},
}