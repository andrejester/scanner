/*
ini ada data Global yang akan kita pakai untuk menyimpan Data untuk handle semua object
*/
var __gData = {} ;
const gData = {
	data:{},
	obj(vaKey,value=null,callBack=null){
		frm.callFunc("gData._obj",[vaKey,value],"root",(par)=>{
			if(callBack !== null) callBack(par) ;
		}) ;
	},
	_obj(vaKey,value=null){
		let retval = null ;
		
		if(typeof vaKey !== "object") vaKey = vaKey.split(",") ;		
		let cVar = "gData.data" ;
		for(const key of vaKey){
			cVar += "['" + key + "']" ;

			if(typeof eval(cVar) == "undefined"){
				eval(cVar + " = {};") ;
			}
		} 
		if(value !== null){
			eval(cVar + " = value ;") ;
		}else{
			retval = eval(cVar) ;
		}

		return retval ;
	},
	Get(vaKey,cDefault=null){
		if(vaKey == "") return this.win.__gData ;

		let cVar = "this.win.__gData" ;
		if(typeof vaKey !== "object") vaKey = vaKey.split(",") ;
		for(const key of vaKey){
			cVar += "['" + key + "']" ;
			if(typeof eval(cVar) == "undefined"){
				return cDefault ;
			}
		}
		return eval(cVar) ;
	},
	Save(vaKey,value){
		// Ambil nilai asal untuk di result, biar kita bisa ambil sekalian update bersamaan
		let old = this.Get(vaKey,null) ;
		let cVar = "this.win.__gData" ;

		if(typeof vaKey !== "object") vaKey = vaKey.split(",") ;
		for(const key of vaKey){
			cVar += "['" + key + "']" ;
			if(typeof eval(cVar) == "undefined"){
				eval(cVar + " = {};") ;
			}
		} 
		if(value == null){
			eval("delete " + cVar + ";") ;
		}else{
			eval(cVar + " = value ;") ;
		}
		return old ;
	},
	_win:null,
	get win(){
		if(this._win == null){
			this._win = a.gWin ;
			if(a.parentNotCrossOrigin(this._win)){
				this._win = this._win.self.parent ;
			}
		} 
		return this._win ;
	},
} ;