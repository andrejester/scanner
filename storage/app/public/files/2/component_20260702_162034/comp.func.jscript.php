<script language="javascript" type="text/javascript">
	var xhr = new XMLHttpRequest() ;
	
	function loadPage(url){
		xhr.open("GET",url);
		xhr.send() ;
		xhr.onload = function(){
			if(xhr.status == 200){
				document.getElementById("frmDiv").innerHTML = xhr.responseText;
			}else{
				console.log("Error load page") ;
			}
		}
	}
</script>