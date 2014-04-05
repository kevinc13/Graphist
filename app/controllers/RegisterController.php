<?php

class RegisterController
{
	public $restful = true;	

	public function getIndex() 
	{
		View::render("register");
	}

	public function postIndex() 
	{
		$this->_vars = RegisterModel::load()->index();
		View::render("register", $this->_vars);
	}
}