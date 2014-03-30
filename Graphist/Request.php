<?php namespace Graphist;

class Request {
	
	public static function URI() 
	{
		$url = parse_url($_SERVER["REQUEST_URI"]);
        $_SERVER["QUERY_STRING"] = (array_key_exists("query", $url))? $url["query"] : "";

        $url["path"] = "/" . rtrim(preg_replace("#".stripslashes(DOCUMENT_ROOT)."#", "", $url["path"], 1), "/");

	    return $url["path"];
	}

	public static function method() 
	{
		return strtoupper($_SERVER['REQUEST_METHOD']);
	}

	public static function getHeadersFromServer()
	{
		return $_SERVER;
	}

}