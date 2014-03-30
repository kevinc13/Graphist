<?php namespace Graphist;

class Config {

	private static $config = array(
		"application" => array(),
		"database" => array(),
		"cache" => array()
	);

	public static function load()
	{
		foreach (glob("app/config/*.json") as $configFile) {
			$contents = file_get_contents($configFile);
			$data = json_decode($contents, true);

			if (stripos($configFile, "application.json")) {
				static::$config["application"] = $data;
			} else if (stripos($configFile, "database.json")) {
				static::$config["database"] = $data;
			} else if (stripos($configFile, "cache.json")) {
				static::$config["cache"] = $data;
			}
		}
	}

	public static function get($configIdentifier)
	{
		list($category, $key) = explode(".", $configIdentifier);

		return static::$config[$category][$key];
	}

}