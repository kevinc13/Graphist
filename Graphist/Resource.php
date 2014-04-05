<?php namespace Graphist;

class Resource {

	protected static $instance;
	
	protected $requestMethod;
	protected $requiredParams = array();
	
	public function __construct()
	{
		$this->graph = Graph::instance()->getClient();
	}

	public static function load()
	{
		if (!isset($instance)) {
			static::$instance = new static;
		}

		return static::$instance;
	}
	
	public function validateRequest()
	{	
    	if ($_SERVER["REQUEST_METHOD"] != $this->requestMethod) {
        	static::error("invalid_request", "Invalid HTTP request method");
    	}
    	
    	foreach ($this->requiredParams as $name) {
        	if ($this->requestMethod == "GET") {
                if (!array_key_exists($name, $_GET)) {
                    static::error("invalid_parameters", "Invalid parameters for requested resource");
                }    	
        	} else {
            	if (!array_key_exists($name, $_POST)) {
                    static::error("invalid_parameters", "Invalid parameters for requested resource");
                }    	
        	}
    	}
	}
	
	public function setRequestMethod($method)
	{
    	$this->requestMethod = strtoupper($method);	
    	return $this;
	}
	
	public function addRequiredParam($name)
	{
    	$this->requiredParams[] = $name;
    	return $this;
	}
	
	public function getUserId()
	{
		return (Request::method() == "GET")? (int)$_GET["user_id"] : (int)$_POST["user_id"];
	}
	
	public function getCurrentTime()
	{
		$UTC = new DateTimeZone("UTC");
		$now = new DateTime("now", $UTC);
		return $now->format(DateTime::RFC1123);
	}
	
	public function getTimeZone()
	{
		$label = Graph::instance()->getClient()->makeLabel("User");
		$userNodes = $label->getNodes("user_id", $this->getUserID());
		$userNode = $userNodes[0];
		
		return $userNode->getProperty("timezone");
	}
	
	public static function error($error, $message, $code = 200)
	{
		$response = Response::create(array(
			"success" => "no"
		));

		$response->setError($error, $message, $code)->toJSON()->send();
		die();
	}	
}