<?php 

class RegisterModel extends BaseModel {

	private $_name;
	private $_email;
	private $_password;
	
	private $_timezone;

	private $_errors;
	private $_success;
	private $_submit;

	private $_user_id;

	public function __construct() 
	{
		parent::__construct();
	}

	public function index() 
	{	
		if ($_POST) {

			if ($this->init()) {
				$this->set("success", true);
				$this->registerSession();
			} else {
				$this->set("success", false);
				$this->set("errors", $this->formatErrors());
			}
		}

		return $this->_vars;
	}

	public function init() {
		$this->_errors  = array();
		$this->_success = false;
		$this->_submit  = isset($_POST['register']);

		$this->_name     = $this->strictFilter($_POST['name']);
		$this->_email    = $this->strictFilter($_POST['email']);
		$this->_password = $this->filter($_POST['password']);
		
		$this->_timezone = $_POST["timezone"];

		$this->_user_id = "";

		if ($this->_submit) {
			$this->validatePost();
		}
		return $this->_success;
	}

	private function strictFilter($var) 
	{
		return (preg_replace("/[^\w\s@\.]/", '', $var));
	}

	private function filter($var) 
	{
		return (preg_replace('/[^\w\s\"\'\.\^&#@!~\+=\?:;<>\(\)\*]$/', '', $var));
	}

	private function validatePost() 
	{
		try 
		{
			if (!isValidTimezoneId($this->_timezone))
			{
				throw new Exception("Looks like something weird happened. Try reloading the page and registering again.");
			}
		
			if ($this->emptyFields()) 
			{
				throw new Exception('You must fill out all fields.');
			}

			if ($this->emailExists())
			{
				throw new Exception("That email is already associated with an account.");
			}
			
			if (!$this->validPass()) 
			{
				throw new Exception('Invalid characters in password.');
			}
			
			if (!$this->validLen($this->_password, "6")) 
			{
				throw new Exception('Password too short.');
			}
			
			if (!$this->validEmail()) 
			{
				throw new Exception('Please enter a valid email address.');
			}

			if (!$this->registerData())
			{
				throw new Exception("Oops, looks like we did something wrong, we could not register you.");
			}

		$this->_success = true;
		} 
		catch (Exception $e) 
		{
			$this->_errors[] = $e->getMessage();
		}
	}

	private function emptyFields() 
	{
		if (empty($this->_name)) {
			return true;
		} elseif (empty($this->_password)) {
			return true;
		} elseif (empty($this->_email)) {
			return true;
		} else {
			return false;
		}
	}

	private function emailExists()
	{
		return false;
	}

	private function validPass() 
	{
		$regex = '/[\w\"\'\.\^\/&#@!~\+=\?:;<>\(\)\*]$/';

		return (preg_match($regex, $this->_password))? true : false;
	}

	private function validEmail() 
	{
		$regex = "/[a-z0-9!#\$%&*+=?\^_`{|}~-]+(?:\.[a-z0-9!#\$%&*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/";

		return (preg_match($regex, $this->_email))? true : false;
	}

	private function validLen($var, $len) 
	{
		return (strlen($var) >= $len)? true : false;
	}

	private function registerData() 
	{
	    $graph = Graph::instance()->getClient();
	
		$UTC = new DateTimeZone("UTC");
		$now = new DateTime("now", $UTC);
		$date = $now->format("Y-m-d H:i:s") . " UTC";
		
		$this->_user_id = $user_id = Graph::instance()->getNextId("user_id");

		$salt = hash("sha512", "$" . $user_id . "$");
		$password = AESCtr::encrypt($this->_password, $salt, 256);
		
		$this->_password = $password;

	    $userLabel = $graph->makeLabel("User");
	    
	    // Create user node
        $node = $graph->makeNode();
        $node->setProperty("user_id", $user_id)
             ->setProperty("name", $this->_name)
             ->setProperty("password", $password)
             ->setProperty("email", $this->_email)
             ->setProperty("created_at", $date)
             ->setProperty("timezone", $this->_timezone)
             ->save();
        
        $node->addLabels(array($userLabel));
	
		return true;
	}

	public function formatErrors() 
	{
		foreach ($this->_errors as $key => $value) {
           return '<div class="message error-message">'.$value.'</div>';
      	}
	}

	public function registerSession()
	{
		$_SESSION['user_id'] = $this->_user_id;
		$_SESSION['name'] = $this->_name;
		$_SESSION['email'] = $this->_email;
		$_SESSION['password'] = $this->_password;
		$_SESSION["timezone"] = $this->_timezone;

		session_regenerate_id();
	}
}