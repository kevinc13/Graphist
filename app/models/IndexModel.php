<?php 

class Index extends BaseModel {

	public function __construct() 
	{
		parent::__construct();
	}

	public function index() 
	{
		if (!empty($_SESSION['email']) and !empty($_SESSION['password'])) 
		{
			
		}
		else if (!empty($_COOKIE["snp-u"]) and !empty($_COOKIE["snp-p"]))
		{

		}
	}

}