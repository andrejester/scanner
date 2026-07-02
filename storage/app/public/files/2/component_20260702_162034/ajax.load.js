/*
Class ini untuk melakukan load component antara lain
1. Load CSS
2. Tab
3. Init Value untuk component
4. DBGRID
*/
const _load = {
	start:0,
	loadCSS(){
		/*
		1. Check apakah ada div cssRoot yang menunjukkan dia membutuhkan css untuk form nya.
		2. Kalau ada kita akan coba cek parent apakah ada cssInduk kalau ada itu yang kita ambil
		3. Jika Tidak ada maka kita akan ambilkan dari Web untuk CSS nya.
		4. Setelah css di load kita baru load component lainnya
		*/
		_load.start = new Date() ;

		let div = a.getById("cssRoot") ;
		if(div !== null){
			let url = div.textContent ;
			a.delObj(div) ;

			frm.callFunc("_load.getCssInduk",[],"",(par)=>{
				if(par == ""){
					fetch(url).then((response) => response.text()).then((script) => {
						_load.applyCSS(script,"Web") ;
					});
				}else{
					_load.applyCSS(par,"Induk") ;
				}
			}) ;
		}else{
			// Lanjutkan Load Component
			_load.Component() ;
		}
	},
	applyCSS(txt,cFrom){
		let css = a.addObj("style",document.head,"cssInduk") ;
		css.textContent = txt ;
		//let nEnd = new Date() ;

		// Melanjutkan ke component selanjutnya
		_load.Component() ;
		document.body.style.opacity = 1 ;
	},
	getCssInduk(){
		let css = "" ;
		let div = a.getById("cssInduk") ;
		if(div !== null){
			css = div.textContent ;
		}
		return css ;
	},
	/*
	Kita akan cek kalau token tidak ada maka akan kita ambilkan dari root dan akan kita coba terus sampai mendapat token
	*/
	nCountToken:500,
	importToken(){
		let token = svr.GetToken() ;
		let appid = svr.GetAppID() ; // Mengambil appid dan menghapus divnya
		
		a.GetCurrentFile() ;		// Mengambil dari div __currentFile dan div kita hapus
		if((token == "" || appid == "") && window.name !== "root") {
			_load.nCountToken += 100 ;
			setTimeout(()=>{_load.importToken()},_load.nCountToken) ;

			// Kalau Token Tidak ada kita akan ambilkan dari Induk
			if(token == ""){
				
				frm.callFunc("svr.GetToken",[],"root",(par)=>{
					if(par !== "") svr._saveToken(par) ;
				}) ;
			}	

			// Kalau appid Tidak ada kita akan ambilkan dari Induk
			if(appid == ""){
				frm.callFunc("svr.GetAppID",[],"root",(par)=>{
					if(par !== "") svr._appid = par ;
				}) ;
			}	
		}
	},
	// Kode atau fungsi yang ingin dijalankan setelah seluruh halaman dan sub-asset selesai diunduh.
	// Dan Setelah Load css
	Component(){
		/*
		Kita Cari Kalau ada iframe src kita tambah __token dan appid
		*/
		let nFrames = 0 ;
		const iframes = document.querySelectorAll("iframe");
		for (const iframe of iframes) {
			// Ini adalah Iframe yang di buat manual bukan dari frm.open
			// kalau dari frm.open maka ada propertie formOpenForm
			if(typeof iframe.fromOpenForm == "undefined"){
				let url = "" ;
				if(typeof iframe.src == "string" && iframe.src !== ""){
					url = iframe.src ;
				}

				if(iframe.id == ""){
					nFrames ++ ;
					iframe.id = "stdFrm_" + nFrames ;
				}
				if(iframe.name == ""){
					iframe.name = iframe.id ;
					iframe.contentWindow.name = iframe.id ;
				} 

				// Mendefinisikan properti src dengan setter dan getter kustom
				iframe.url = url ;
				iframe.src = "" ;
				Object.defineProperty(iframe, 'src', {
					get: function() {
						return this.url ;
					},
					set: function(newValue) {
						this.url = newValue ;
						frm.urlOpen(newValue,this.name) ;
					},
				});

				if(url !== "") iframe.src = url ;
			}
		}

		/*
		Simpan Token Ke Form Aktif
		*/
		_load.importToken() ;

		/*
		Urutan Menjalankan OnLoad
		1. Kalau ada Tab buat Format Tab
		2. Init Value untuk Component
		3. DBGRID
		*/
		// Kalau ada Tab akan kita format datanya
		let divTab = document.querySelectorAll('#_tab_div_main_');
		for(const div of divTab){
			let va = a.str2JSON(div.textContent) ;
			va = va.getRow ;
			tab.createTab(div,va) ;
		}

		// Kita Jalankan unsyncronus biar tidak menghambat buka form
		const forms = document.querySelectorAll("form");
		for (const form of forms) {
			for(const field of form){
				// init Text
				txt.init(field) ;
				// Mengatur Component Required
				txt.initRequired(field) ;
			}
		}

		// Dia kalau ada Configurasi di buttonallowed dengan status false maka Button Kita Dell, ini biar tidak bisa di inspect sama user
		// Biar dia posisi button bisa di pakai button lain.
		// Bukan Menggunakan style.visibility ( visibility kita gunakan kalau mau lokasi masih di block oleh button itu)
		gData.obj(["frm","buttonAllowed"],null,(data)=>{
			let form = document.form1 ;
			if(typeof data == "object" && form !== null){
				for(const key in data){
					if(typeof data[key] !== "undefined" && !data[key] && typeof form[key] !== "undefined"){
						let txtID = form[key].id.substring(4) ;
						a.delObj(form[key]) ;
						a.delById("required-" + txtID) ;
					}
				}
			}
		}) ;

		// Check Apakah di Body ada Configurasi DBGRID kalau ada akan kita inisialisasi
		let divGrid = document.querySelectorAll('#_dbg_div_main_');
		for(const div of divGrid){
			let va = a.str2JSON(div.textContent) ;
			va = va.getRow ;
			eval(va.conf.name + "= new main_dbgrid(va,div,true) ;") ;
		}

		// Kita Cek apakah dia berhak membuka halaman ini
		frm.isAllowedPage() ;
	}
}

// Kode atau fungsi yang ingin dijalankan setelah DOM selesai dimuat.
// Kita akan load css dengan mengambil dari Induk biar tidak selalu mengambil dari web
document.addEventListener('DOMContentLoaded', ()=>{	
	// Body kita hidden dulu biar proses load css tidak kelihatan.
	document.body.style.opacity = 0 ;
	setTimeout(()=>{
		document.body.style.opacity = 1 ;
	},1000) ;

	_load.loadCSS() ;
});

// Tambah Event window.onload untuk Check Tombol
window.addEventListener("load",()=>{
	/*
	Kita Edit Button Field nya Bisa di tambahkan berdasarkan Standart Nama Object nya
	*/
	setTimeout(()=>{ 
		const buttons = document.getElementsByTagName('input');
		for(const button of buttons){		
			a.setButtonIcon(button) ;
		}	
	},200) ;
}) ;


// Hanya aktif di window utama, bukan di iframe
if (window.self === window.top) {
  if (!window.idleSession) {
    window.idleSession = {
      //idleLimit: 900,
      idleLimit: 3000,
      lastActivityTime: Date.now(),
      lastPing: 0,
      timeoutDisplay: null,
      pingServer(timeNow) {
        a.ajax(svr.GetComponentPath() +"/activity", "KeepAlive", "time=" + timeNow);
      },

      pingServerThrottled() {
        const now = Date.now();
        if (now - this.lastPing > 30000) {
          this.lastPing = now;
          this.pingServer(this.lastActivityTime);
        }
      },

      resetTimer(event) {
        this.lastActivityTime = Date.now();
        this.pingServerThrottled();
      },

      formatTime(seconds) {
        const m = Math.floor(seconds / 60).toString().padStart(2, '0');
        const s = (seconds % 60).toString().padStart(2, '0');
        return `${m}:${s}`;
      },

      countdown() {
        if (!this.timeoutDisplay) this.timeoutDisplay = sBar.getItem("cTimeout");
        const now = Date.now();
        const idleTime = Math.floor((now - this.lastActivityTime) / 1000);
        const remaining = this.idleLimit - idleTime;

        this.timeoutDisplay.innerText = `Sesi Anda akan berakhir dalam: ${this.formatTime(remaining)}`;

        if (remaining <= 60) {
          Object.assign(this.timeoutDisplay.style, {
            color: "red",
            backgroundColor: "red",
            fontWeight: "bold",
            animation: "blinker 1s step-start infinite",
            padding: "6px",
            borderRadius: "6px"
          });

          if (remaining <= 0) {
            a.ajax(svr.GetComponentPath()+"/autologout", "AutoLogout()", "", function (obj) {
							if (typeof obj.data["Url"] !== "undefined") {
								open(obj.data["Url"]+"?data=" + encodeURIComponent(obj.data["Param"])+"&prev="+encodeURIComponent(obj.data["Prev"]), "_parent");
							}else{
								open(__BASE_URL__,"_parent") ;
							}
							
            });
          }
        } else {
          Object.assign(this.timeoutDisplay.style, {
            color: "",
            backgroundColor: "",
            fontWeight: "",
            animation: "",
            padding: "",
            borderRadius: ""
          });
        }
      },

      init() {
        this.pingServer(this.lastActivityTime);

        ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {
          document.addEventListener(evt, this.resetTimer.bind(this), { passive: true });
        });

        setInterval(() => this.countdown(), 1000);
      }
    };
  }
}else{
	['click','mousemove', 'keydown', 'scroll', 'touchstart'].forEach(evt => {//'mousemove'
		document.addEventListener(evt, function () {
			if (window.parent !== window) {
			frm.sendMessage({type:"activity"});
				//window.parent.postMessage({ type: "activity" }, "*");
			}
		}, { passive: true });
	});
}