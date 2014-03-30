<?php namespace Graphist;

use \PDO;
use Graphist\Config;
use Graphist\Database\Connection;

class DB {

	public static $connection;

	public static function connection($parameters)
	{
		if (!static::$connection) {
			static::$connection = new Connection(static::pdo($parameters));
		}

		return static::$connection;
	}

	public static function pdo(array $parameters)
	{
		return new PDO("mysql:host=".$parameters["host"].";dbname=".$parameters["database"], $parameters["username"], $parameters["password"]);
	}

	public static function table($table)
	{
		return static::connection()->table($table);
	}

}