<?php
/*
|--------------------------------------------------------------------------
| Rest Local Config
|--------------------------------------------------------------------------
|
| Rest Local config kita gunakan untuk melakukan configurasi yang akan kita butuhkan
| untuk pengambilangan melalui controller dengan perintah $this->GetConfig($key)
| Cara Penulisan pada Local Config menggunakan variable $config [key] = value ;
|
| Contoh :
| $config['data_path'] = /test/coba ;
|
|--------------------------------------------------------------------------
| nb.
| Kalau Anda Mendefinikasn key pada localconfig sama dengan global config 
| maka yang akan di pakai adalah yang ada di localconfig
|
| Contoh :
| Pada file global_config ada variable
| $config['global_ip_blacklist'] = '' ;
|
| Apabila pada file rest_local_config anda membuat variable yang sama
| $config['global_ip_blacklist'] = '192.168.0.1, 192.168.0.10' ;
|
| Maka di controller yang akan di pakai adalah yang pada rest_local_config
| Usahakan dalam membuat key jangan kembar dengan global config
|
|--------------------------------------------------------------------------
| definisi pada local config ada baiknya di awali dengan kata local contoh
| $config['local_data_path'] = ./test/coba ;
| sehingga anda bisa dengan mudah mengenali type key apakah local atau global.
*/
// $config['local_data_path'] = "./data" ;
//$config['_URL_SW']  		= "http://dev1.sis1.net/assist-switching_cbd/public/api/";