<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$cPath = __DIR__ . '/../connect.php';
require_once($cPath) ;
$tes = cds::GetUDF("http://bpr.submodule.sis2.net/sub-bpr-system","ScanFolder::ScanTmpDir",array('oke'));
print_r($tes) ;

