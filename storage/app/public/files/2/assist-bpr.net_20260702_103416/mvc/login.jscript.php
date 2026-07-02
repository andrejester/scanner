<script language="javascript" type="text/javascript">
function Form_onLoad(){
	initForm() ;
  CheckOnline() ;
}

var nRefresh = 59 ;
function CheckOnline(){
  setTimeout(CheckOnline,1000) ;
	nRefresh ++ ;
  if(nRefresh >= 60){
    nRefresh = 0 ;
    LoadCaptCha() ;
  } 
}

function cUserName_onBlur(){
	LoadCaptCha() ;
}

function initForm(){
  a.f.cPassword.value = "" ;
  fieldfocus(document.form1.cUserName) ;
}

function LoadCaptCha(){
	var dDate = new Date()
	var o = document.getElementById("oCaptCha") ;
  o.src = __BASE_URL__ + "login/captcha?t=" + dDate.getTime() + "&appid=" + svr.GetAppID() ;
} 

function cmdLogin_onClick(){
  a.ajax("","dologin",a.fContent(),function(cData,cStatus){
		let vaData = JSON.parse(cData) ;
		if(typeof vaData["data"] !== "undefined"){
			if(vaData["data"] == "ok"){
				open(__BASE_URL__,"_parent") ;
			}else{
				a.alert(vaData["data"],"Warning !",function(){
					initForm() ;
				}) ;
			}
		}
    
  }) ;
} 
</script>