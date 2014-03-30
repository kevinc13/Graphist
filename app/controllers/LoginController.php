<?php

class LoginController extends BaseController {

	public $restful = true;

	public function getIndex() 
	{
		Login::load()->index();
		View::render("login");
	}

	public function postIndex() 
	{
		$this->_vars = Login::load()->index();
		View::render("login", $this->_vars);
	}

}