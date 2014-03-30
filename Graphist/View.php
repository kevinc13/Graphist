<?php namespace Graphist;

class View
{
	private static $root = "app/views/";

	public static function render($view, $vars = null) 
	{
		if (!is_null($vars)) 
		{
			extract($vars);
		}

		if (stripos($view, ".")) {
			$path = str_replace(".", "/", $view) . EXT;
		} else {
			$path = $view . EXT;
		}

		if (static::exists($path)) {
			require_once static::$root . $path;
		} else {
			Response::error("404");
		}
	}

	public static function insert($partial) {
		require_once "app/partials/{$partial}.php";
	}

	public static function exists($path) 
	{	
		return (file_exists(static::$root . $path))? true : false;
	}
}