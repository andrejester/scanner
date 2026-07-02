<?php
class ProgressBar{
  var $Width = 100 ;
  var $Height = 20 ;
  var $Name = "pr1" ;
  function Show($nWidth=0,$nHeight=0){
    $cFileName = $this->Name ;
    
    if(!empty($nWidth)){
      $this->Width = $nWidth ;
    }
    
    if(!empty($nHeight)){
      $this->Height = $nHeight ;
    }
    echo('<iframe class="cell_row" id="' . $cFileName . '" scrolling="no" style="border:0px" height="' . $this->Height . '" width="' . $this->Width . '" src="' . compFolder() . '/progressbar/progressbar.pr.php"></iframe>') ;
  }
}
$pr = new ProgressBar ;
?>