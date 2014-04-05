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
			static::execute($controller, $method, $parameters);
		}
	}

	public static function resolve($controller) 
	{
		$model = str_replace("Controller", "", $controller);

		if (!static::load($controller, $model)) return;

		return new $controller;
	}

	public static function execute($controller, $method, $parameters = array()) 
	{
		if (property_exists(get_class($controller), "restful") && $controller->restful) 
		{
			$action = strtolower(Request::method()) . ucfirst($method);
		} 
		else 
		{
			$action = $method;
		}

		call_user_func_array(array($controller, $action), $parameters);
	}

	protected static function load($controller, $model) 
	{
		if (file_exists($controllerPath = "app/controllers/" . $controller . EXT)) 
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
}