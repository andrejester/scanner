<script language="javascript" type="text/javascript">
var nMaxWidth = 0 ;
var oP = null ;

objPr = {
  Value : function(nValue){
    nValue = Math.min(Math.max(nValue,0),100) ;
    
    oP.style.width = nValue * nMaxWidth / 100 ;
  },
}

function InitPr(){
  var o = document.getElementById("divProgressBar") ;
  oP = document.getElementById("imgProgressBar") ;
  nMaxWidth = oP.offsetWidth ;
  
  with(o.style){
    width = window.innerWidth - 4 ;
    height = window.innerHeight - 4 ;
  }
  
  self.parent.pr1 = objPr ;
}
</script>