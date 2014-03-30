<?php namespace Graphist;

class Controller {

	public $restful = false;

	public function __construct() { }

	public static function call($destination, $parameters = array()) 
	{
		$parts = explode("#", $destination);

		if (count($parts) > 1) 
		{
			list($name, $method) = $parts;
		} 
		else 
		{
			$name = $parts[0];
			$method = "index";	
		}

		$controller = static::resolve($name);

		if (is_null($controller)) 
		{
			Response::error("404");
		} 
		else 
		{
			$controller->execute($method, $parameters);
		}
	}

	public static function resolve($controller) 
	{
		$model = $controller;

		if (!static::load($controller, $model)) return;

		$controller = static::format($controller);

		return new $controller;
	}

	public function execute($method, $parameters = array()) 
	{
		if ($this->restful) 
		{
			$action = strtolower(Request::method()) . ucfirst($method);
		} 
		else 
		{
			$action = "action" . ucfirst($method);
		}

		call_user_func_array(array($this, $action), $parameters);
	}

	protected static function load($controller, $model) 
	{
		$controller = strtolower(str_replace('.', '/', $controller));

		if (file_exists($controllerPath = "app/controllers/" . ucfirst($controller) . "Controller" . EXT)) 
		{
			require_once $controllerPath;

			if (file_exists($modelPath = "app/models/" . ucfirst($model) . "Model" . EXT)) 
			{
				require_once $modelPath;
			}

			return true;
		}

		return false;
	}

	protected static function format($controller) 
	{
		return ucfirst($controller) . "Controller";
	}

}