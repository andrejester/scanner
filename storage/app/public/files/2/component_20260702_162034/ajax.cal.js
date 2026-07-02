const cal = {
	calWin:null,back:null,field:null,table:null,cellBulan:null,date:null,selBulan:null,selTahun:null,
	isClose:true,obj:null,
	show(field){
		cal.field = field ;

		let rect = field.getBoundingClientRect() ;
		let vaPos = {"top":rect.bottom+3,"left":rect.left-2} ;
		let data = {"tgl":field.value,fieldHeight:rect.height} ;
		frm.sendMessage(data,"mainFrame","cal.openRemoteCal",cal.pickRemoteCal,vaPos) ;
	},
	openRemoteCal(data){
		let field = {value:data.data.tgl} ;
		cal.obj = data ;

		cal.isClose = false ;
		if(!a.isDateValided(field.value)){
			cal.date = new Date() ;
		}else{
			cal.date = new Date(Date2String(field.value)) ;
		}
		cal.field = field ;

		// Buat Background
		cal.back = a.addBack("_cal_background_",null,0) ;
		(function(cal){
			cal.back.onclick = function(){cal.closeCal()} ;
		})(cal) ;

		// Buat Box Untuk Grid
		let vaPos = txt.globalPos() ;
		let fieldHeight = data.data.fieldHeight ;
		let nWidth = 250 ;
		let nHeight = 270 ;
		let nTop = vaPos.top - 2 ;
		let nLeft = vaPos.left + 2 ;
		const css = "top:" + nTop + "px;left:" + nLeft + "px;width:" + nWidth + "px;height:" + nHeight + "px;overflow:hidden;border-radius:4px" ;
		cal.calWin = a.addObjById("_cal_main_win_","div",null,"browse_main",css) ;

		// Buat Table
		cal.table = a.addObjById("_cal_table_","table",cal.calWin,null,"border-spacing: 0px;border-collapse: collapse;cell-padding:0px;cell-spacing:0px;width:100%;height:" + nHeight + "px") ;
		cal.createCal() ;			// Kita Membuat Tamplet Calendar nya
		cal.loadDate() ;			// Kita akan isi tamplate nya dengan tanggal
		let div = cal.calWin ;
		nWidth = div.offsetWidth ;
		nHeight = div.offsetHeight ;
		
		// Jika Tinggi Melebihi tinggi Window maka kita geser ke atas calendarnya
		if(nTop + nHeight + 15 > vaPos.scr.bottom){
			nTop = Math.max(0,vaPos.top - nHeight - 5 - fieldHeight) ;
		}

		// Jika Batas Kanan Lebih Besar Dari Screen Kita Geser Kekiri
		if(div.offsetLeft + div.offsetWidth + 15 > vaPos.scr.right){
			let nMove = div.offsetLeft + div.offsetWidth - vaPos.scr.right + 15
			nLeft = nLeft - nMove ;
		}

		cal.calWin.style.top = nTop ;
		cal.calWin.style.left = nLeft ;

		cal.objIndex() ;
	},
	pickRemoteCal(data){
		cal.field.value = data.tgl ;
		fieldfocus(cal.field) ;
		cal.closeCal()
	},
	getMonth(nMonth){
		let vaMonth = ["Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"] ;
		return vaMonth[nMonth] ;
	},
	sortSelect(selElem) {
		let tmpAry = new Array();
		for (let i=0;i<selElem.options.length;i++) {
			tmpAry[i] = new Array();
			tmpAry[i][0] = selElem.options[i].text;
			tmpAry[i][1] = selElem.options[i].value;
		}
		tmpAry.sort();
		while (selElem.options.length > 0) {
			selElem.options[0] = null;
		}
		for (let i=0;i<tmpAry.length;i++) {
			let op = new Option(tmpAry[i][0], tmpAry[i][1]);
			selElem.options[i] = op;
		}
		return;
	},
	loadDate(){
		// method untuk Memasukkan Tanggal di calendar
		// Jika Tahun >= Max dari Select maka kita akan tambah 10 tahun ke depan
		if(cal.selTahun.max <= cal.date.getFullYear()){
			let i = cal.selTahun.max ;
			for(let n=0;n<=10;n++){
				let option = a.addObj("option",cal.selTahun);
  			option.text = n+i ;
				option.value = n+i ;
			}
			cal.selTahun.max = i+10 ;
		}

		// Jika Tahun < Min maka kita akan tambah tahun lebih kecil 10 tahun
		if(cal.selTahun.min >= cal.date.getFullYear()){
			let i = cal.selTahun.min - 1 ;
			for(let n=i;n>=i-9;n--){
				let option = a.addObj("option",cal.selTahun);
  			option.text = n ;
				option.value = n ;
			}
			cal.selTahun.min = i-9 ;
			cal.sortSelect(cal.selTahun) ;
		}
		
		cal.selBulan.value = cal.date.getMonth() + 1 ;
		cal.selTahun.value = cal.date.getFullYear() ;

		let date = new Date(cal.date) ;
		let _d = new Date(cal.startDate(date)) ;
		let nBulan = cal.selBulan.value ;
		for(row=0;row<6;row++){
			for(col=0;col<7;col++){
				let tgl = cal.padL(_d.getDate(),2) + "-" + cal.padL((_d.getMonth() + 1),2) + "-" + _d.getFullYear() ; 
				let cell = cal.table.rows[row+2].cells[col] ;
				cell.className = tgl == cal.field.value ? "dbg_cell_body2 dbg_cell_body_click2" : "dbg_cell_body2" ;
				if(col == 0) cell.style.color = "red" ;
				cell.classOut = cell.className ;
				cell.tgl = tgl ;
				cell.textContent = _d.getDate() ;
				cell.title = _d.getDate() + " " + cal.getMonth(_d.getMonth()) + " " + _d.getFullYear() ;
				cell.style.opacity = cal.date.getMonth() == _d.getMonth() ? 1 : 0.5 ;

				_d = cal.nextDay(_d) ;
			}
		}
	},
	padL(par,len){
		par = "0000" + par ;
		return par.substring(par.length-len) ;
	},
	closeCal(){
		cal.isClose = true ;		
		a.delObj([cal.back,cal.calWin]) ;
	},
	nextDay(date){
		let _d = new Date(date) ;
		_d.setDate(_d.getDate()+1) ;
		return _d ;
	},
	startDate(date){
		let _d = new Date(date) ;
		_d.setDate(1) ;
		_d.setDate(1-_d.getDay()) ;
		return _d ;
	},
	body(){
		for(row=0;row<6;row++){
			let tr = a.addObj("tr",cal.table) ;
			for(col=0;col<7;col++){
				let cell = a.addObj("td",tr,null,"dbg_cell_body2","text-align:center") ;
				cell.tgl = "" ;
				(function(cell,cal){
					cell.onclick = function(){cal.cellClick(cell)} ;
					cell.onmouseover = function(){cal.cellOver(cell,0)} ;
					cell.onmouseout = function(){cal.cellOver(cell,1)} ;
				})(cell,cal) ;

				cell.textContent = "_" ;
			}
		}
	},
	cellClick(cell){
		let data = {"tgl":cell.tgl} ;
		frm.responseMessage(data,cal.obj["par"]["caller"],cal.obj["par"]["call_id"]) ;
		cal.closeCal()
	},
	cellOver(cell,par){
		let className = typeof cell.classOut == "undefined" ? "dbg_cell_body2" : cell.classOut ;
		if(par == 0){
			className = "dbg_cell_body2 dbg_cell_body_click2" ;
		}
		cell.className = className ; 
	},objIndex_Count:-1,
	objIndex(){
		if(cal.objIndex_Count < a.setObjIndex()){
			a.setObjIndex(cal.back) ;
			cal.objIndex_Count = a.setObjIndex(cal.calWin) ;
		}
		if(!cal.isClose) setTimeout(cal.objIndex,300) ;
	},
	header(){
		// Membuat row header
		let tr = a.addObj("tr",cal.table) ;
		let cell = a.addObj("td",tr,null,null,"text-align:center") ;
		cell.colSpan = 7 ;

		// Didalam Row Header kita beri table tiga colom
		let tb = a.addObj("table",cell,null,null,"width:100%;border:0px;boder-padding:0px;border-spacing: 0px;cell-spacing:0px;cell-padding:0px")
		tr = a.addObj("tr",tb) ;

		// Kolom paling kiri kita beri span untuk prev month dan prev year
		let spanStyle = "display:inline-block;font-size:16px;overflow:hidden;cursor:pointer;border-radius:2px;" ;
		spanStyle += "font-weight:bold;min-width:25px;max-width:25px;box-sizing: border-box;min-height: 24px;max-height: 24px;line-height: 13px;" ;
		cell = a.addObj("td",tr,null,null,"text-align:center;cursor:default;width:55px") ;

		// Prev Year
		let span = a.addObj("img",cell,null,"dbg_cell_body2 no_txt_select",spanStyle + ";margin-right:2px;") ; // &#8647;
		span.src = svr.GetComponentURL() + "themes/icons/global/prev-year.gif?_th=auto" ;
		span.title = "Mundur satu tahun" ;
		(function(span,cal){
			span.onclick = function(){cal.navClick(span,'prev-year')} ;
			span.onmouseover = function(){cal.cellOver(span,0)} ;
			span.onmouseout = function(){cal.cellOver(span,1)} ;
		})(span,cal) ;

		// Prev Month
		span = a.addObj("img",cell,null,"dbg_cell_body2 no_txt_select",spanStyle) ; // &#8592;
		span.src = svr.GetComponentURL() + "themes/icons/global/prev-month.gif?_th=auto" ;
		span.title = "Mundur satu bulan" ;
		(function(span,cal){
			span.onclick = function(){cal.navClick(span,'prev-month')} ;
			span.onmouseover = function(){cal.cellOver(span,0)} ;
			span.onmouseout = function(){cal.cellOver(span,1)} ;
		})(span,cal) ;

		// Bagian tengah kita beri keterangan bulan dan tahun
		cal.cellBulan = a.addObj("td",tr,null,"no_txt_select","text-align:center;cursor:default;font-weight:bold") ;

		let cssBulan = "display:inline-block;font-size:16px;overflow:hidden;cursor:pointer;border-radius:2px;" ;
		cssBulan += "width:100%;box-sizing: border-box;min-height: 24px;max-height: 24px;" ;
		let divBulan = a.addObj("div",cal.cellBulan,null,"dbg_cell_body2 no_txt_select",cssBulan) ;
		// Select Bulan
		cal.selBulan = a.addObj("select",divBulan,null,null,"border:0px;background:transparent;display:inline-block;margin-top:-4px;") ;
		(function(cal){
			cal.selBulan.onchange = function(){
				cal.date.setDate(1) ;
				cal.date.setMonth(cal.selBulan.value-1) ;
				cal.loadDate() ;
			} ;
		})(cal) ;
		for(n=0;n<12;n++){
			let option = a.addObj("option",cal.selBulan);
  		option.text = cal.getMonth(n).substring(0,3) ;
			option.value = n + 1;
		}

		let date = new Date() ;
		cal.selTahun = a.addObj("select",divBulan,null,null,"border:0px;background:transparent;display:inline-block;margin-top:-4px;") ;
		cal.selTahun.min = date.getFullYear() - 50 ;
		cal.selTahun.max = date.getFullYear() + 25 ;
		(function(cal){
			cal.selTahun.onchange = function(){
				cal.date.setDate(1) ;
				cal.date.setYear(cal.selTahun.value) ;
				cal.loadDate() ;
			} ;
		})(cal) ;
		for(n=cal.selTahun.min;n<=cal.selTahun.max;n++){
			let option = a.addObj("option",cal.selTahun);
  		option.text = n ;
			option.value = n ;
		}

		// Kolom paling kanan kita beri span untuk next month dan next year
		cell = a.addObj("td",tr,null,"no_txt_select","text-align:center;cursor:default;width:55px") ;

		// Next Month
		span = a.addObj("img",cell,null,"dbg_cell_body2 no_txt_select",spanStyle + ";margin-right:2px;") ; // &#8594;
		span.src = svr.GetComponentURL() + "themes/icons/global/next-month.gif?_th=auto" ;
		span.title = "Maju satu bulan" ;
		(function(span,cal){
			span.onclick = function(){cal.navClick(span,'next-month')} ;
			span.onmouseover = function(){cal.cellOver(span,0)} ;
			span.onmouseout = function(){cal.cellOver(span,1)} ;
		})(span,cal) ;

		// Next Year
		span = a.addObj("img",cell,null,"dbg_cell_body2 no_txt_select",spanStyle) ; // &#8649;
		span.src = svr.GetComponentURL() + "themes/icons/global/next-year.gif?_th=auto" ;
		span.title = "Maju satu tahun" ;
		(function(span,cal){
			span.onclick = function(){cal.navClick(span,'next-year')} ;
			span.onmouseover = function(){cal.cellOver(span,0)} ;
			span.onmouseout = function(){cal.cellOver(span,1)} ;
		})(span,cal) ;
		
		let hari = ["Min","Sen","Sel","Rab","Kam","Jum","Sab"] ;
		tr = a.addObj("tr",cal.table,null,"no_txt_select") ;
		for(col=0;col<7;col++){
			cell = a.addObj("td",tr,null,"dbg_cell_header2","text-align:center;font-weight:normal;width:14.4%") ;
			cell.textContent = hari[col] ;
			if(col == 0) cell.style.color = "red" ;
		}
	},
	navClick(span,type){
		cal.date.setDate(1) ;
		if(type == "prev-year"){
			cal.date.setYear(cal.date.getFullYear()-1) ;
		}else if(type == "prev-month"){
			cal.date.setMonth(cal.date.getMonth()-1) ;
		}else if(type == "next-month"){
			cal.date.setMonth(cal.date.getMonth()+1) ;
		}else if(type == "next-year"){
			cal.date.setYear(cal.date.getFullYear()+1) ;
		}
		cal.loadDate() ;
	},
	footer(){
		let tr = a.addObj("tr",cal.table) ;
		let date = new Date() ;
		let cell = a.addObj("td",tr,null,"dbg_cell_body2","text-align:center;cursor:pointer","[ Today : " + date.getDate() + " " + cal.getMonth(date.getMonth()) + " " + date.getFullYear() + " ]") ;
		cell.colSpan = 7 ; 
		(function(cell,date){
			cell.onclick = ()=>{
				let data = {"tgl":cal.padL(date.getDate(),2) + "-" + cal.padL(date.getMonth()+1,2) + "-" + date.getFullYear()} ;
				frm.responseMessage(data,cal.obj["par"]["caller"],cal.obj["par"]["call_id"]) ;

				cal.closeCal() ;
			} ;
		})(cell,date) ;
	},
	createCal(){
		cal.table.innerHTML = "" ;
		cal.header() ;
		cal.body() ;
		cal.footer() ;
	},
};