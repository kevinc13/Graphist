<?php namespace Graphist\Routing;

use Graphist\Controller as Controller;
use \Closure;

class Route {

	public $uri;
	public $method;

	public $action;
	public $parameters;

	public function __construct($method, $uri, $action, $parameters = array()) 
	{
		$this->uri = $uri;
		$this->method = $method;
		$this->action = $action;

		$this->parameters = (array) $parameters;
	}

	public function call() 
	{
		if ($this->action instanceof Closure)
		{
			call_user_func_array($this->action, $this->parameters);
		}
		else
		{
			Controller::call($this->action, $this->parameters);
		}
	}

	public static function any($uri, $action = null) 
	{
		if (!is_null($action))
		{
			Route::get($uri, $action);
			Route::post($uri, $action);
		}
	}

	public static function get($uri, $action = null) 
	{
		if (!is_null($action)) 
		{
			Router::add("GET", $uri, $action);
		}
	}

	public static function post($uri, $action = null) 
	{
		if (!is_null($action)) 
		{
			Router::add("POST", $uri, $action);
		}	
	}

}