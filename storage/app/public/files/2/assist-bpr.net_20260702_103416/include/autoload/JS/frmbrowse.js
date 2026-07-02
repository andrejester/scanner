const lookup = {
	url:svr.GetBaseURL() + "system/frmbrowse/",
  SeekKode(field,cTable,lNama = true){ 
		let cKeterangan = eval("a.f.cNama" + field.name.substring(1)) ;
		let additionalCallback = {} ;
		if(lNama){
			additionalCallback = {"Keterangan":cKeterangan};
		}
		field.Browse("SeekKode","cKode="+field.value+"&cTable="+cTable,additionalCallback,true,this.url) ;
	},
	SeekKodeAo(field,cCabang=''){ 
		let cKeterangan = eval("a.f.cNama" + field.name.substring(1)) ;
		field.Browse("SeekKodeAo","cKode="+field.value+"&cCabang="+cCabang,{"Nama":cKeterangan},true,this.url) ;
	},
	SeekKodeCoa(field){ 
		let cKeterangan = eval("a.f.cNama" + field.name.substring(1)) ;
		field.Browse("SeekKodeCoa","cKode="+field.value,{"Keterangan":cKeterangan},true,this.url) ;
	},
	SeekRekening(field,cTable,callbackFunction = null){
		let label        = cTable.charAt(0).toUpperCase() + cTable.slice(1);
		const self       = this ;
		let prefixKode 	 = field.name.substring("cFrekuensi".length); 
		let cCabang      = eval("a.f.cCabang" + prefixKode) ;
		let cGolongan    = eval("a.f.cGolongan" + prefixKode) ;
		let cUrut        = eval("a.f.cUrut" + prefixKode) ;
		let cFrekuensi   = eval("a.f.cFrekuensi" + prefixKode) ;
		
		if(field.value != ""){
			field.value = padlAll(field) ;
			a.wait() ; 
			a.ajax(this.url,"SeekRekening",a.fContent([cCabang,cGolongan,cUrut,cFrekuensi])+"&cTable="+label+"&prefixKode="+prefixKode,function(obj){
				a.endwait() ;
				if(obj.dataRows > 0){
					if(obj.getRow['Error'] != ""){
						frm.snackBar(obj.getRow['Error'],"error") ;
						self.initDataNasabah(prefixKode); 
					}else{
						frm.obj2Field(obj) ;
						if (callbackFunction !== null && typeof callbackFunction === 'function') {
						  callbackFunction();
						}
					}
				}else{
					frm.snackBar("Rekening Tidak Ditemukan !","error") ;
					self.initDataNasabah(prefixKode); 
				}
			});
		}
	},
	SeekRekeningLama(field,cTable){
		let label = cTable.charAt(0).toUpperCase() + cTable.slice(1);
		field.Browse("SeekRekeningLama","cKode="+field.value+"&cTable="+cTable,function(obj){
			var vaRek = obj["Rekening"];
      var vaSplit = vaRek.split(".") ;
      if(vaSplit.length == 4) { 
        with(a.f){
					var cCabang      = eval("cCabang" + label) ;
					var cGolongan    = eval("cGolongan" + label) ;
					var cUrut        = eval("cUrut" + label) ;
					var cFrekuensi   = eval("cFrekuensi" + label) ;
					cCabang.value    = vaSplit[0] ;
					cGolongan.value  = vaSplit[1] ;
					cUrut.value      = vaSplit[2] ;
					cFrekuensi.value = vaSplit[3] ;
					fieldfocus(cFrekuensi);
				}
      }
		},true,this.url) ;
	},
	SeekKodeCif(field,cTable){
		if(field.value != ""){
			let regex = /^[0-9]+$/;
			let isAngka = regex.test(field.value);
			if(isAngka){
				if(field.value != ""){
					field.value = padlAll(field) ;
				}
			}
			let prefixKode = field.name.substring("cKode".length); 

			field.Browse("SeekKodeCif","cKode="+field.value+"&cTable="+cTable,function(obj){
				let dataCif = ["cNama","cAlamat","cCabangNasabah"];
				dataCif.forEach(function(item) {
					var dataField = eval("a.f." + item+prefixKode) ;
					if (typeof dataField !== 'undefined') {
						dataField.value = obj[item.substring(1)] ;
					}
				});
			},true,this.url) ;
		}
	},
	CariRekeningNasabah(field,cTable){
		if(field.value != ""){
			field.Browse("CariRekening","cKode="+field.value+"&cTable="+cTable,{},true,this.url) ;
		}
	},
	SeekRekeningNew(field,cTable,lDetail=false,callbackFunction = null){
		let regex = /^[A-z]/;
		let isAngka = regex.test(field.value);
		if(!isAngka){
			let label        = cTable.charAt(0).toUpperCase() + cTable.slice(1);
			const self       = this ;
			if(field.value != ""){
				field.value = padlAll(field) ;
				a.wait() ; 
				a.ajax(this.url,"SeekRekeningNew","cRekening="+field.value+"&cTable="+cTable+"&lDetail="+cTable,function(obj){
					a.endwait() ;
					if(obj.dataRows > 0){
						if(obj.getRow['Error'] != ""){
							frm.snackBar(obj.getRow['Error'],"error") ;
							self.initDataNasabah(label); 
						}else{
							self.CekField(obj);
							if (callbackFunction !== null && typeof callbackFunction === 'function') {
								callbackFunction();
							}
						}
					}else{
						frm.snackBar("Rekening Tidak Ditemukan !","error") ;
						self.initDataNasabah(label); 
					}
				});
			}
		}else{
			if(field.value != ""){
				//field.value = padlAll(field) ;
				field.Browse("CariRekening","cKode="+field.value+"&cTable="+cTable,{},true,this.url) ;
			}
		}
	},
	initDataNasabah(label){
		let dataTabungan = ["cGolongan"+label,"cUrut"+label,"cFrekuensi"+label,"cCabang"+label,"cNama"+label,"cAlamat"+label,"nSaldo"+label];
		dataTabungan.forEach(function(item) {
			var dataField = eval("a.f." + item) ;
			if (typeof dataField !== 'undefined') {
				dataField.value = "" ;
			}
		});
		let cCabang = eval("a.f.cCabang" + label) ;
		fieldfocus(cCabang);
	},
	CekField(obj){
		var va = {};
		for(const [key,value] of Object.entries(obj.getRow)){
			var elements = document.querySelectorAll('[name*="' + key + '"]');
			if (elements.length > 0){
				va[key] = value ;
			}
		}
		// Memperbarui nilai obj.getRow dengan hasil dari CekField
		delete obj.getRow;
		obj.getRow = va;
		frm.obj2Field(obj) ;				
	},
	OpenFormSeekRekening(JenisRekening,field){//a.f.cURukt
		OpenForm(a.GetBaseURL() + 'system/trcari/index/'+JenisRekening+"/"+field.name,'FrmCari','Pencarian',450,400,'');
	}
}