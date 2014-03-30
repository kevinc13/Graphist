<?php namespace Graphist;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{
	public static $instance;
	public $loggers = array();

	public function __construct() {}

	public static function instance()
	{
		if (!isset(static::$instance)) {
			static::$instance = new Log();
		}

		return static::$instance;
	}

	public function getLogger($name)
	{
		if (!array_key_exists($name, $this->loggers)) {
			$this->loggers[$name] = $this->createLogger($name);
		}

		return $this->loggers[$name];
	}

	public function createLogger($name)
	{
		$name = "graphist_{$name}.log";
		$logger = new Logger($name);

		$logger->pushHandler(new StreamHandler("storage/logs/{$name}", Logger::DEBUG));
		$logger->addNotice("Logger initialized");

		return $logger;
	}
}