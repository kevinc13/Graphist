<?php namespace Graphist\Model;

use Graphist\DB as DB;

abstract class BaseModel {

	protected $_db;
	protected $_vars;
	protected static $_instance;

	protected function __construct() 
	{ 
		$this->_vars = array();
	}

	protected function set() 
	{
		if (func_num_args() == 2) 
		{
			$key   = func_get_arg(0);
			$value = func_get_arg(1);

			$this->_vars[$key] = $value; 
		}
		else 
		{
			$bundle = func_get_arg(0);

			foreach ($bundle as $key => $value) {
				$this->_vars[$key] = $value;
			}
		}
	}

	protected function get($key) 
	{
		return $this->_vars[$key];
	}

	public static function load() {
		if (!static::$_instance) {
			static::$_instance = new static;
		}
		
		return static::$_instance;
	}
}