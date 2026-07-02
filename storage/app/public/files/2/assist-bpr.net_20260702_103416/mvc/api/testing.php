<?php 
	$cShell = exec("pgrep -l -f -a php",$cOutput);
	print_r($cShell);
?>