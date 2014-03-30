<?php

class ConsoleModel extends BaseModel {

	public function __construct() 
	{
		parent::__construct();
	}

	public function index() {

		return $this->_vars;
	}
}