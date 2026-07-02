<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan ataralain
1. GET = index_get()
2. POST = index_post()
3. PUT = index_put()
4. DELETE = index_delete()
*/
class atm_bepede_Controller extends MVC_Controller {
	function index(){
		//http_response_code(400);
		PostingATM::Proses() ;
	}
}