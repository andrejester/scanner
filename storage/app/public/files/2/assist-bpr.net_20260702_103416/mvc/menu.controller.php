<?php
class menu_Controller extends MVC_Controller {
	function Menu_Logout(){
		User::Delete() ;
		SaveSetting("cLogin",0) ;
		echo('window.open("./","_self");') ;
	}
}