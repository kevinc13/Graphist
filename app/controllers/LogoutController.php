<?php 

class LogoutController extends BaseController {

	public function actionIndex() 
	{
		if (isset($_COOKIE["snp-u"]) || isset($_COOKIE["snp-p"]))
		{
			setcookie("snp-u", "", time()-3600);
			setcookie("snp-p", "", time()-3600);	
		}
		
		View::render("logout");
	}

}