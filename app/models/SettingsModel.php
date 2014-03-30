<?php 

class SettingInputException extends Exception {

	public function __construct($message, $code = "0", Exception $previous = null) 
	{
		parent::__construct($message, $code, $previous);
	}

	public function toString() 
	{
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}

}

class Settings extends BaseModel {
	public function __construct() 
	{
		parent::__construct();
	}

	public function index()
	{
		if (empty($_SESSION['user_id']) || empty($_COOKIE["access_token"])) Response::to_route("/");
		
		$this->set("viewName", "Settings");
	
		$accountStatus = DB::table("snp_users")
		                   ->where("user_id", "=", $_SESSION["user_id"], "")
		                   ->get(array("account_status AS status"));

		if ($accountStatus[0]->status !== "ACTIVE")
		{
			$this->set("unregistered", true);
		}
		else
		{
			$this->set("unregistered", false);
		}
		
		$userProfileImgIDs = DB::table("snp_user_profile")->where("user_id", "=", $_SESSION["user_id"], "")->get(array("profile_image", "profile_cover_image", "profile_background_image"));
		
		if (!is_null($userProfileImgIDs[0]->profile_image))
		{
			$profileImgData = DB::table("snp_images")
                            ->where("image_id", "=", $userProfileImgIDs[0]->profile_image, "")
                            ->get(array("image_path"));

	        if ($_SESSION["profile_info"]["profile_image"] != $profileImgData[0]->image_path)          
	        {
	            $_SESSION["profile_info"]["profile_image"] = $profileImgData[0]->image_path;
	        }	
		}
		else
		{
			$_SESSION["profile_info"]["profile_image"] = "public/img/default_profile_image_50.png";
		}
		
		if (!is_null($userProfileImgIDs[0]->profile_cover_image))
		{
			$coverImgData = DB::table("snp_images")->where("image_id", "=", $userProfileImgIDs[0]->profile_cover_image, "")->get(array("image_path"));
			
			if ($_SESSION["profile_info"]["profile_cover_image"] != $coverImgData[0]->image_path)          
	        {
	            $_SESSION["profile_info"]["profile_cover_image"] = $coverImgData[0]->image_path;
	        }
		}
		else
		{
			$_SESSION["profile_info"]["profile_cover_image"] = "public/img/default_profile_cover_image.jpg";
		}
	
		$numNotifications = DB::table("snp_notifications")->where("receiver", "=", $_SESSION['user_id'], "")
	    								->get(array("COUNT(notification_id) as numNotifications"));
		$_SESSION['numNotifications'] = $numNotifications[0]->numNotifications;

		$this->set("numNotifications", $numNotifications[0]->numNotifications);

		$numImage = DB::connection()->query("SELECT AUTO_INCREMENT AS count FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME ."' AND TABLE_NAME = 'snp_images'");

		$this->set("numImage", $numImage[0]->count);
		
		$dropdownHTML = "";
		
		foreach (getTimeZones() as $key => $value)
		{
			if ($_SESSION["timezone"] == $value)
			{
				$dropdownHTML .= "<option selected=\"selected\" value=\"{$value}\">{$key}</option>";
			}
			else
			{
				$dropdownHTML .= "<option value=\"{$value}\">{$key}</option>";
			}
		}
		
		$this->set("dropdownHTML", $dropdownHTML);

		return $this->_vars;
	}

	public function save($setting)
	{
		if (empty($_SESSION['user_id']) || empty($_COOKIE["access_token"])) Response::to_route("/");
	
		$accountStatus = DB::table("snp_users")
		                   ->where("user_id", "=", $_SESSION["user_id"], "")
		                   ->get(array("account_status AS status"));

		if ($accountStatus[0]->status !== "ACTIVE")
		{
			$this->set("unregistered", true);
		}
		else
		{
			$this->set("unregistered", false);
		}
		
		$userProfileImgIDs = DB::table("snp_user_profile")->where("user_id", "=", $_SESSION["user_id"], "")->get(array("profile_image", "profile_cover_image", "profile_background_image"));
		
		if (!is_null($userProfileImgIDs[0]->profile_image))
		{
			$profileImgData = DB::table("snp_images")
                            ->where("image_id", "=", $userProfileImgIDs[0]->profile_image, "")
                            ->get(array("image_path"));

	        if ($_SESSION["profile_info"]["profile_image"] != $profileImgData[0]->image_path)          
	        {
	            $_SESSION["profile_info"]["profile_image"] = $profileImgData[0]->image_path;
	        }	
		}
		else
		{
			$_SESSION["profile_info"]["profile_image"] = "public/img/default_profile_image_50.png";
		}
	
		$numNotifications = DB::table("snp_notifications")->where("receiver", "=", $_SESSION['user_id'], "")
	    								->get(array("COUNT(notification_id) as numNotifications"));
		$_SESSION['numNotifications'] = $numNotifications[0]->numNotifications;

		$this->set("numNotifications", $numNotifications[0]->numNotifications);
		
		$dropdownHTML = "";
		
		foreach (getTimeZones() as $key => $value)
		{
			if ($_SESSION["timezone"] == $value)
			{
				$dropdownHTML .= "<option selected=\"selected\" value=\"{$value}\">{$key}</option>";
			}
			else
			{
				$dropdownHTML .= "<option value=\"{$value}\">{$key}</option>";
			}
		}
		
		$this->set("dropdownHTML", $dropdownHTML);

		try 
		{
			switch ($setting) {
				case 'account':
					$this->validateAccount();
					break;	
				case 'password':
					$this->validatePassword();
					break;
				case 'profile':
					$this->validateProfile();
					break;
				case 'privacy':

					break;
			}	

			$this->set("success", "yes");
			$this->set("message", "Your settings have been saved.");
		} 
		catch (SettingInputException $e) 
		{
			$this->set("success", "no");
			$this->set("message", $e->getMessage());
		}

		return $this->_vars;
	}

	private function filter($var)
	{
		return preg_replace('/[^a-zA-Z0-9@-_\.\|\/\#\$=\+\(\)\*%&\^\s]/', "", htmlentities($var, ENT_QUOTES));
	}

	private function validateAccount()
	{
		if (!empty($_POST['username']))
		{
			$username = $this->filter($_POST['username']);

			if ($username != $_SESSION['username'])
			{
				$check = DB::table("snp_users")->where("username", "=", $username, "")->count();
				
				if ($check == 1) {
					throw new SettingInputException("The username you entered has already been taken.");
				} else {
					DB::table("snp_users")->where("user_id", "=", $_SESSION['user_id'], "")->update(array( "username" => $username ));
					$_SESSION['username'] = $username;
				}
			}
		}

		if (!empty($_POST['email']))
		{
			$email = $this->filter($_POST['email']);

			if ($email != $_SESSION['email'])
			{
				DB::table("snp_users")->where("user_id", "=", $_SESSION['user_id'], "")->update(array( "email" => $email ));
				$_SESSION['email'] = $email;
			}
		}

		if (!empty($_POST['name']))
		{
			$name = $this->filter($_POST['name']);

			if ($name != $_SESSION['name'])
			{
				DB::table("snp_users")->where("user_id", "=", $_SESSION['user_id'], "")->update(array( "name" => $name ));
				$_SESSION['name'] = $name;
			}
		}
		
		if (!empty($_POST["timezone"]))
		{
			$timezone = $_POST["timezone"];
			
			if (isValidTimeZoneId($timezone))
			{
				if ($timezone != $_SESSION["timezone"])
				{
					DB::table("snp_users")->where("user_id", "=", $_SESSION['user_id'], "")->update(array( "timezone" => $timezone ));
					$_SESSION["timezone"] = $timezone;
				}
			}
			else
			{
				throw new SettingInputException("Invalid Timezone Identifier.");
			}
		}
	}

	private function validatePassword()
	{
		if (!empty($_POST['current_password']))
		{
            $salt = hash("sha512", "$" . $_SESSION["user_id"] . "$");
            
			if ($_POST['current_password'] != AESCtr::decrypt($_SESSION["password"], $salt, 256))
			{
				throw new SettingInputException("Your current passwords do not match.");
			}


			if ($_POST["new_password"] != $_POST["verify_new_password"])
			{
				throw new SettingInputException("Your new passwords do not match.");
			}

			if (strlen($_POST["new_password"]) < 6)
			{
				throw new SettingInputException("Your password must be at least 6 characters.");
			}

			$regex = '/[\w\"\'\.\^\/&#@!~\+=\?:;<>\(\)\*]{6,}$/';

			if (!preg_match($regex, $_POST['new_password']))
			{
				throw new SettingInputException("Your password contains invalid characters.");
			}
            
            // Create the AES CBC-Mode password
            $encryptedPassword = AESCtr::encrypt($_POST["verify_new_password"], $salt, 256);

            // Update database
			DB::table("snp_users")->where("user_id", "=", $_SESSION['user_id'], "")->update( array( "password" => $encryptedPassword ) );

            // Update Session
			$_SESSION["password"] = $encryptedPassword;
            
            // Generate new access token
            $accessTokenSalt = substr($salt, 0, 40);
         
            $accessToken = AESCtr::encrypt($encryptedPassword, $accessTokenSalt, 256);
		 
            // Update session access token
		    $_SESSION["accessToken"] = $accessToken;
		    
            // Update cookie
		    setcookie("access_token", $accessToken, time() + 60 * 60 * 24 * 30);  
		}
		else
		{
			throw new SettingInputException("Please enter your current password.");
		}
	}

	private function validateProfile()
	{
		$infoArray = array("introduction", "interests", "likes", "education", "wishes", "favorite_quote");

		$settings = array();

		foreach ($_POST as $key => $value)
		{
			if (in_array($key, $infoArray))
			{
				if (!empty($value))
				{
					$settings[$key] = htmlentities($value, ENT_QUOTES);
				}
				else
				{
					$settings[$key] = null;
				}
			}
		}

		if (!empty($settings))
		{
			try 
			{
				DB::table("snp_user_profile")->where("user_id", "=", $_SESSION["user_id"], "")->update($settings);
			} 
			catch (PDOException $e) 
			{
				throw new SettingInputException($e->getMessage());
			}

		}

		foreach ($settings as $key => $value) 
		{
			$_SESSION["profile_info"][$key] = $value;	
		}
	}

}