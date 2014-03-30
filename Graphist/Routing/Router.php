<?php namespace Graphist\Routing;

class Router {

	//public static $package;

	private static $_routes = array(
		"GET" => array(),
		"POST" => array()
	);

	private static $_patterns = array(
		"(:num)" => "([0-9]+)",
		"(:alpha)" => "([a-zA-Z0-9\.\-_%=]+)",
		"(:all)" => "(.*)",
		"(?:num)" => "([0-9]+)?",
		"(?:alpha)" => "([a-zA-Z0-9\.\-_%=]+)?",
		"(?:all)" => "(.*)?"
	);

	private static $_methods = array("GET", "POST");

	public static function add($method, $route, $action) 
	{
		if (in_array($method, static::$_methods)) 
		{
			if (!array_key_exists($route, static::$_routes[$method])) 
			{
				static::$_routes[$method][$route] = $action;
			}
		}
	}

	public static function match($method, $uri) 
	{
		if ($uri[0] != "/") 
		{
			$uri = "/" . $uri;
		}

		// We will go through all routes for the request method 
		// and return a new Route instance if the URI matches 
		// and false if it doesn't.
		foreach (static::method($method) as $route => $action) 
		{
			// Perform a regular expression match if the route
			// contains wildcards.
			if (strpos($route, "(:")) 
			{
				// Compile the route into a valid regular expression.
				$pattern = '#^'.static::wildcards($route).'$#';

				if (preg_match($pattern, $uri, $parameters)) 
				{
					// If we get a match we'll return the route and remove the first
					// parameter match, as preg_match sets the first array item to the
					// full-text match of the pattern.
					return new Route($method, $uri, $action, array_slice($parameters, 1));
				}
			} 
			else 
			{	
				// Perform a literal match.
				if ($uri === $route) return true;
			}
		
		}

		return false;
	}

	public static function wildcards($key) 
	{
		return strtr($key, static::$_patterns);
	}

	public static function method($method) 
	{
		return static::$_routes[$method];
	}

	public static function action($method, $uri) 
	{	
		if ($uri[0] != "/") 
		{
			$uri = "/" . $uri;
		}

		if ($route = static::match($method, $uri)) 
		{
			return $route->action;
		} 
		else 
		{
			return -1;
		}
	}

	public static function route($method, $uri) 
	{
		$routes = (array) static::method($method);

		if (array_key_exists($uri, $routes)) 
		{
			$action = $routes[$uri];

			return new Route($method, $uri, $action);
		}

		if (($route = static::match($method, $uri)) !== false) 
		{
			return $route;
		}

	} 

	public static function routes()
	{
		return static::$_routes;
	}
}