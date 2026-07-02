<?php

require_once "../../component/mvcload.php" ;
	
// Load Configurasi Dari include/assist.php
	if(SisConfig::Init()){
		$app = new SisMVC(SisMVC::MVC_SERVER) ;
		$app->Start() ;
	}