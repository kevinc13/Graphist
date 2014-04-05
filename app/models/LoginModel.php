<?php 

class Login extends BaseModel {

	private $_user_id;
	private $_name;
	private $_username;
	private $_password;
	private $_password_encrypted;
	private $_timezone;

	private $_errors = array();
	private $_success;
	private $_submit;

	public function __construct() 
	{
		parent::__construct();
	}

	public function index() 
	{
		if (isset($_SESSION["user_id"])) 
		{
			Response::to_route("console");
		}
		else if (!empty($_COOKIE["snp-u"]) and !empty($_COOKIE["snp-p"]))
		{
			
		}
		else if ($_POST) 
		{
			if (!empty($_POST['username']) || !empty($_POST['password'])) 
			{
				if ($this->init()) {
					Response::to_route("console");
				} else {
					$this->set("errors", $this->formatErrors());
				}
			}
			else
			{
				$this->set("errors", $this->formatErrors());
			}
		}

		return $this->_vars;	
	}

	public function init()
	{
		$this->_errors   = array();
		$this->_success  = false;
		$this->_submit   = isset($_POST['login'])? true : false;

		$this->_user_id      = 0;
		$this->_name     = '';
		$this->_username = ($this->_submit)? $this->filter($_POST["username"]) : $_SESSION["username"];
		$this->_password = ($this->_submit)? $this->filter($_POST["password"]) : "password";

		if ($this->_submit) {
	    	$this->validatePost();
        }
		
	    if (isset($_POST['remember']) && $this->_success == true) {
		    $this->registerCookie();  
		}
		    
        return $this->_success;
	}

	private function filter($var)
	{
		return preg_replace('/[^a-zA-Z0-9@-_\.]/','',$var);
	}

	private function validatePost()
	{
		try {
			if (!$this->validateData()) {
			    throw new Exception('Invalid username and password combination.');
			}

	        $this->_success = true;
	        $this->registerSession();
		} catch (Exception $e) {
			$this->_errors[] = $e->getMessage();
		}
	}

	private function validateSession()
	{
        if ($this->sessionExists()) {
        	$this->_success = true;
        }
    }

    private function validateData()
    {
    	$graph = Graph::instance()->getClient();

    	$userLabel = $graph->makeLabel("User");
    	$userNodes = $userLabel->getNodes("username", $this->_username);

    	if (count($userNodes) > 0) {

    		$userNode = $userNodes[0];
    		$matcher = AESCtr::decrypt($userNode->getProperty("password"), hash("sha512", "$" . $userNode->getProperty("user_id") . "$"), 256);

    		if ($this->_password != $matcher) {
                return false;
            }

            $this->_name = $userNode->getProperty("name");
            $this->_user_id = $userNode->getProperty("user_id");
            $this->_password_encrypted = $userNode->getProperty("password");
            $this->_timezone = $userNode->getProperty("timezone");

            return true;
    	} else {
    		return false;
    	}
    }

    private function registerSession()
    {
		$_SESSION['user_id'] = $this->_user_id;
		$_SESSION['name'] = $this->_name;
		$_SESSION['username'] = $this->_username;
		$_SESSION['password'] = $this->_password_encrypted;
		$_SESSION["timezone"] = $this->_timezone;
    }
     
	private function generateAccessToken() {
		$salt = substr(hash("sha512", "$" . $this->_user_id . "$"), 0, 40);
		 
		$accessToken = AESCtr::encrypt($this->_password_encrypted, $salt, 256);
		 
		$_SESSION["accessToken"] = $accessToken; 
		 
		setcookie("access_token", $accessToken, time() + 60 * 60 * 24 * 30); 
	}

	private function registerCookie() {
		if (isset($_POST['remember'])) {
		    setcookie("snp-u", $this->_username, time() + 60 * 60 * 24 * 30);
		  	setcookie("snp-p", AESCtr::encrypt($this->_password, hash("sha512", "$" . $this->_user_id . "$"), 256), time() + 60 * 60 * 24 * 30);
		} 
	}

	public function formatErrors() {
		foreach ($this->_errors as $key=>$value) {
			return '<div class="message error-message">'.$value.'</div>';
		}
	}
}