<?php namespace Graphist;

use Graphist\Foundation\Runnable as Runnable;
use Graphist\Routing\Router as Router;
use \Closure as Closure;

class Application implements Runnable {

	/**
	 * Current URI Requested
	 * 									
	 * @var string
	 */
	private $_uri;

	/**
	 * Current HTTP Request Method
	 *
	 * @var string
	 */
	private $_method;
	
	/**
	 * Singleton Application Instance
	 *
	 * @var string
	 */
	public static $singleton;
	
	/**
	 * Creates a new instance of the Application class,
	 * sets the uri and http method of the incoming 
	 * request, and loads the application.
	 * 
	 * @param  string $uri    
	 * @param  string $method
	 * @return void 
	 */
	public function __construct($uri, $method) {
		$this->_uri    = $uri;
		$this->_method = $method;
		
		$this->load();
	}

	/**
	 * Run Application
	 * 
	 * @return void
	 */
	public static function run() {
		
		if (!isset(static::$singleton))
		{
			static::$singleton = new Application(Request::URI(), Request::method());
		}
		
		return static::$singleton;
	}

	/**
	 * Finally, we load the application, basically 
	 * routing the request.		
	 * 
	 * @return void
	 */
	private function load() {
		if (Router::match($this->_method, $this->_uri) !== false) {
			$route = Router::route($this->_method, $this->_uri);

			$route->call();
		} else {
			Response::error("404");
		}
	}
	
	public static function fatal(Closure $closure)
	{
		set_error_handler($closure);
	}
}