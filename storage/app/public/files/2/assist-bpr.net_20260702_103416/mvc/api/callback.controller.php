<?php
/* 
Berisi Script Controller dengan method yang bisa kita definisikan ataralain
1. GET = index_get()
2. POST = index_post()
3. PUT = index_put()
4. DELETE = index_delete()
*/
class callback_Controller extends MVC_Controller {
	function index(){
		AuthClient::CallBack();
	}
}