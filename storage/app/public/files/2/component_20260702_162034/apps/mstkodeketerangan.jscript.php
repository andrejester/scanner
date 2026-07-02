<script language="javascript" type="text/javascript">
function Form_onLoad(){
	getEdit(false) ;
	LoadGrid() ;
}

function LoadGrid(){
  a.ajax('','LoadGrid',a.f.cTableName) ;
}

var lEdit = false ;
function getEdit(lPar,nAction){
  lEdit = lPar ;
  frm.setupComponent(a.f,lPar) ;
  a.f.nPos.value = nAction ; 
  if(lPar){
    if(nAction == 1) initValue() ;
    fieldfocus(a.f.cKode) ;
  }else{   
   	initValue() ;
  }
}

function initValue(){
	frm.initValue() ;
}

function DBGRID1_onClick(vaRow,nCol){
	if(!lEdit){
		a.f.cKode.value = vaRow.Kode ;
		a.f.cKeterangan.value = vaRow.Keterangan ;
	}
}

function cKode_onBlur(field){
	if(a.f.cKode.value !== "")(		
		a.ajax("","SeekKode",a.fContent([field,a.f.cTableName]),function(obj){
  		// Jika Tidak Ketemu
			if(obj.dataRows == 0){
				if(a.f.nPos.value != 1){
					frm.snackBar("Data Tidak Ditemukan .....","error") ;
					getEdit(false) ;
				}
			}else{
				frm.obj2Field(obj) ;
				if(a.f.nPos.value == 1){
					frm.snackBar("Kode Sudah Ada, Transaksi Tidak bisa Dilanjutkan ....","error") ;
					getEdit(false) ;
				}else{
					// Jika Di hapus
					if(a.f.nPos.value == 3){
						ConfirmDelete(field) ;
					}
				}
			}
		})
	)
}

function ConfirmDelete(field){
	a.confirm("Data akan dihapus ?","",function(par){
		if(par){
			a.wait() ;
			a.ajax('','DeleteData',a.fContent([field,a.f.cTableName]),function(obj){
				a.endwait() ;
				if(obj.data == "ok"){
					frm.snackBar("Data sudah dihapus !","info") ;
					getEdit(false) ;
					LoadGrid() ;
				}
			}) ;
		}else{
			getEdit(false) ;
		}
	}) ;
}

function cmdCancel_onClick(field){
	getEdit(false) ;
}

function cmdSave_onClick(field){
	frm.save("","","",function(obj){
		if(obj.data == "ok"){
			getEdit(false) ;
			LoadGrid() ;
		}else{
			a.alert(obj.data,"Error ....",function(){
				getEdit(false) ;
				LoadGrid() ;
			}) ;
		}
	})
}
</script>