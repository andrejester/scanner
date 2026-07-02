<?php
	error_reporting(0) ;	
  function errorCustom($errno, $errstr, $errfile, $errline){
	
		switch ($errno) {
			case E_DEPRECATED:
					return true; 
			default:
				$key = "$errfile $errline"; 
				//
				//
				echo json_encode("Data Element Tidak Valid") ;
				exit() ;
				break;
			}
			echo json_encode("Data Element Tidak Valid") ;
			exit();
	}
	
	function cleanString($string) {
	  //$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
		$str 		= preg_replace('/[^a-zA-Z0-9\/. ]/', '', $string); // Removes special chars.
		return $str ;
	}

	register_shutdown_function(function () {
			if (error_get_last()) {
					# ambil error terakhir
					$error = (object) error_get_last();

					errorCustom(
							$error->type, $error->message, $error->file, $error->line
					);
			}
	});
	set_error_handler("errorCustom");
?>