<?php namespace Graphist\Foundation;

abstract class Facade {

	protected static $instance;
	
	protected static $app;
	
	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() {}

	/**
	 * Resolve the facade instance
	 *
	 * @param  string  $name
	 * @return mixed
	 */
	protected static function resolveFacadeInstance($name)
	{
		if (is_object($name)) return $name;

		if (isset(static::$instance))
		{
			return static::$instance;
		}

		return static::$instance = static::$app[$name];
	}

	/**
	 * Get the application instance behind the facade.
	 *
	 * @return Application
	 */
	public static function getFacadeApplication()
	{
		return static::$app;
	}

	/**
	 * Set the application instance.
	 *
	 * @param  Application  $app
	 * @return void
	 */
	public static function setFacadeApplication($app)
	{
		static::$app = $app;
	}

	/**
	 * Handle static method calls
	 *
	 * @param  string  $method
	 * @param  array   $args
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		$instance = static::resolveFacadeInstance(static::getFacadeAccessor());

		switch (count($args))
		{
			case 0:
				return $instance->$method();

			default:
				return call_user_func_array(array($instance, $method), $args);
		}
	}
	
}