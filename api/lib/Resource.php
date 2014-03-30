<?php

use Graphist\Auth\Controller\ResourceController;
use Graphist\Request;

class Resource {

	private $resourceController;
	private $userId;
	
	private $requestMethod;
	private $requiredParams = array();
	
	public function __construct() {
	}
	
	public function authorize()
	{
    	/*$headers = apache_request_headers();
		
		$authorizationHeader = null;
        if (isset($headers['AUTHORIZATION'])) {
            $authorizationHeader = $headers['AUTHORIZATION'];
        }

        if (null !== $authorizationHeader) {
            // Decode AUTHORIZATION header into PHP_AUTH_USER and PHP_AUTH_PW when authorization header is basic
            if (0 === stripos($authorizationHeader, 'basic')) {
                $exploded = explode(":", urldecode(base64_decode(substr($authorizationHeader, 6))));
                
                if (count($exploded) == 2) {
                    list($this->user_id, $accessToken) = $exploded;
                }
            }
        } else {
            static::error("not_authorized", "Your request has not been authorized.", 401);
        }
		
		$password = DB::table("snp_users")->where("user_id", "=", $this->user_id, "")->get(array("password"));
		
		if (!empty($password))
		{
			$password = $password[0]->password;
			
			$salt = hash("sha512", "$" . $this->user_id . "$");
			
			if (AESCtr::decrypt(urldecode($accessToken), $salt, 256) != $password)
			{
				static::error("not_authorized", "Your request has not been authorized.", 401);
			}
		}
		else
		{
			static::error("not_authorized", "Your request has not been authorized.", 401);
		}*/
	}
	
	public function validateRequest() {
    	
    	if ($_SERVER["REQUEST_METHOD"] != $this->requestMethod) {
        	$this->error("invalid_request", "Invalid HTTP request method");
    	}
    	
    	foreach ($this->requiredParams as $name) {
        	
        	if ($this->requestMethod == "GET") {
                if (!array_key_exists($name, $_GET)) {
                    $this->error("invalid_parameters", "Invalid parameters for requested resource");
                }    	
        	} else {
            	if (!array_key_exists($name, $_POST)) {
                    $this->error("invalid_parameters", "Invalid parameters for requested resource");
                }    	
        	}
    	
    	}
    	
	}
	
	public function setRequestMethod($method) {
    	$this->requestMethod = strtoupper($method);
    	
    	return $this;
	}
	
	public function addRequiredParam($name) {
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
		$userNodes = $label->getNodes("user_id", $this->getUID());
		$userNode = $userNodes[0];
		
		return $userNode->getProperty("timezone");
	}
	
	public function error($error, $message, $code = 200)
	{
		$response = Response::create(array(
			"success" => "no"
		));

		$response->setError($error, $message, $code)->toJSON()->send();
		die();
	}
	
}