const svr = {
	_token:"",
	_saveToken(par){
		let c = svr.GetToken() ;
		if(c == "" && par !== "") svr._token = par ;
	},
	GetToken(){
		if(svr._token == ""){			
			let url = window.location
			const params = new URLSearchParams(url.search);
			
			let c = params.get("__token") ;
			if(c !== null && c !== "") svr._token = c ;
			
			let divToken = a.getById("divToken") ;
			if(divToken !== null){
				svr._token = divToken.textContent ;
				a.delObj(divToken) ;
			} 
		}
		return svr._token ;
	},
	_appid:"",
	GetAppID: function(winName=null,lCallParent=true){
		winName = winName == null ? window.name : winName ;		
		if(svr._appid == ""){
			svr._appid = gData.Get(["frm",winName,"svr","appid"]) ;
			let url = window.location
			const params = new URLSearchParams(url.search);

			let c = params.get("appid") ;
			if(c !== null && c !== "") svr._appid = c ;
			
			let divAppID = a.getById("divAppID") ;
			if(divAppID !== null){
				svr._appid = divAppID.textContent ;
				a.delObj(divAppID) ;
			} 

		}

		return svr._appid ;
	},
	_csrfToken:"",
	GetcsrfToken: function(winName=null,lCallParent=true){
		winName = winName == null ? window.name : winName ;		
		/*
		if(svr._csrfToken == ""){
			//svr._csrfToken = gData.Get(["frm",winName,"svr","csrfToken"]) ;
			let divcsrfToken = a.getById("divcsrfToken") ;
			if(divcsrfToken !== null){
				svr._csrfToken = divcsrfToken.textContent ;
				a.delObj(divcsrfToken) ;
			} 
		}
		*/
		//selalu consume jika ada div untuk monolitik karena selalu ambil main ketika ada getedit
		let divcsrfToken = a.getById("divcsrfToken") ;
		if(divcsrfToken !== null){
			svr._csrfToken = divcsrfToken.textContent ;
			a.delObj(divcsrfToken) ;
		}
		return svr._csrfToken ;
	},
	/*
	GetCookie(key){
		if(typeof svr._cookie == "undefined"){
			let cookieArray = document.cookie.split(';'); // Pisahkan setiap pasangan key=value menjadi elemen array
			svr._cookie = {} ;
			for(const data of cookieArray){
				let cookiePair = data.split('='); // Pisahkan key dan value
				svr._cookie [cookiePair[0].trim()] = decodeURIComponent(cookiePair[1]);
			}
		}
		let retval = typeof svr._cookie [key] !== "undefined" ? svr._cookie [key] : "" ;
		return retval ;
	},
	*/
	GetCookie(key){
		let cookieArray = document.cookie.split(';'); // Pisahkan setiap pasangan key=value menjadi elemen array
		let retval = "" ;
		for(const data of cookieArray){
			let cookiePair = data.split('='); // Pisahkan key dan value
			if(key == cookiePair[0].trim()){
				retval = decodeURIComponent(cookiePair[1]) ;
			}
		}
		return retval ;
	},
	IsMVC: function(winName=null){
		winName = winName == null ? window.name : winName ;

		return gData.Get(["frm",winName,"svr","mvc"]) ;
  },
	GetComponentURL: function(winName=null){
		winName = winName == null ? window.name : winName ;

		return gData.Get(["frm",winName,"svr","compurl"]) ;
	},
	GetBaseURL: function(winName=null){
		winName = winName == null ? window.name : winName ;

		return gData.Get(["frm",winName,"svr","baseurl"]) ;
	},
	GetComponentPath: function(winName=null){
		winName = winName == null ? window.name : winName ;

		return gData.Get(["frm",winName,"svr","compfolder"]) ;
	},
	GetComponentVersion: function(winName=null){
		winName = winName == null ? window.name : winName ;

		return gData.Get(["frm",winName,"svr","compver"]) ;
	},
	GetDirURL: function(winName=null){
		winName = winName == null ? window.name : winName ;

		return gData.Get(["frm",winName,"svr","dirURL"]) ;
	},
	GetController: function(winName=null){
		winName = winName == null ? window.name : winName ;

		return gData.Get(["frm",winName,"svr","controller"]) ;
	},
	IsDevelopmentMode(winName){
		let lRetval = true ;
		if(Svr.IsMVC(winName)){
			let url = svr.GetBaseURL() + "/" ;
			nPos = url.indexOf("/public/") ;
			if(nPos >= 0){
				lRetval = false ;
			}
		}
		return lRetval ;		
	},
};

// Masukkan Kedalam Variable svr biar mudah ambilnya
gData.Save(["frm",window.name,"svr"],{"mvc":__COMPONENT_TYPE__ == "mvc","compurl":__COMP_URL__,"baseurl":__BASE_URL__,"compfolder":__COMPONENT_FOLDER__,"appid":__APP_ID__,"compver":__COMPONENT_VERSION__,"dirURL":__DIR_URL__,"controller":__CONTROLLER__}) ; //"csrftoken":__CSRF_TOKEN__,