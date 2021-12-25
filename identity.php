<?php

namespace Identity;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class Identity
{
	private $Entity;
	private $Language;
	private $Configs;

	private $Users;
	private $Profiles;
	private $LoginTypes;
	private $Tokens;
	private $Roles;
	private $UserRoles;
	private $Phones;
	private $Addresses;
	
	public $Config;

	public $CurrentUser;

	public function __construct($Entity, $Language = null)
	{
		$this->Language = $Language;
		$this->Entity = $Entity;
		$this->Initialize();
	}
	
	private function Initialize()
	{
		$this->InitializeConstants();
		$this->InitializeIncludes();
		$this->InitializeEntities();
	}
	
	private function InitializeConstants()
	{
		if(!defined("DS"))
		{
			define("DS", DIRECTORY_SEPARATOR);
		}
		
		define("IDENTITY_BASE_PATH", dirname(__FILE__));
		define("IDENTITY_CORE_PATH", IDENTITY_BASE_PATH . DS . "core");
		define("IDENTITY_LANGUAGE_PATH", IDENTITY_CORE_PATH . DS . "language");
		define("IDENTITY_MODEL_PATH", IDENTITY_BASE_PATH . DS . "model");
	}

	private function InitializeIncludes()
	{
		# Core
		require_once(IDENTITY_CORE_PATH . DIRECTORY_SEPARATOR . "session.php");
		require_once(IDENTITY_CORE_PATH . DIRECTORY_SEPARATOR . "security.php");
		require_once(IDENTITY_CORE_PATH . DIRECTORY_SEPARATOR . "mail.php");

		# Models
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "user.php");
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "profile.php");
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "logintype.php");
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "token.php");
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "role.php");
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "userrole.php");
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "phone.php");
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "address.php");
		require_once(IDENTITY_MODEL_PATH . DIRECTORY_SEPARATOR . "config.php");
	}

	private function InitializeEntities()
	{
		$this->Configs = $this->Entity->AddTable("config", "Identity\Model\Config");
		$this->Users = $this->Entity->AddTable("users", "Identity\Model\User");
		$this->Profiles = $this->Entity->AddTable("profiles", "Identity\Model\Profile");
		$this->LoginTypes = $this->Entity->AddTable("logintypes", "Identity\Model\LoginType");
		$this->Tokens = $this->Entity->AddTable("tokens", "Identity\Model\Token");
		$this->Roles = $this->Entity->AddTable("roles", "Identity\Model\Role");
		$this->UserRoles = $this->Entity->AddTable("userroles", "Identity\Model\UserRole");
		$this->Phones = $this->Entity->AddTable("phones", "Identity\Model\Phone");
		$this->Addresses = $this->Entity->AddTable("addresses", "Identity\Model\Address");

		$this->Entity->InitializeEntities(["config", "users", "profiles", "logintypes", "tokens", "roles", "userroles", "phones", "addresses"]);

		$this->InitializeData();
	}

	private function InitializeData()
	{
		$this->Config = $this->Configs->Get(1);

		# Config
		if($this->Config == null)
		{
			$this->Config = new \Identity\Model\Config();
			$this->Config->Id = 1;
			$this->Configs->Add($this->Config);

			# Create default Login Types
			if($this->LoginTypes->Get(1) == null)
			{
				$Intern = new \Identity\Model\LoginType();
				$Intern->Name = "Intern";
				$this->LoginTypes->Add($Intern);				
			}

			if($this->LoginTypes->Get(2) == null)
			{
				$FB = new \Identity\Model\LoginType();
				$FB->Name = "Facebook";
				$this->LoginTypes->Add($FB);				
			}

			if($this->LoginTypes->Get(3) == null)
			{
				$GOOGLE = new \Identity\Model\LoginType();
				$GOOGLE->Name = "Google";
				$this->LoginTypes->Add($GOOGLE);				
			}

			if($this->LoginTypes->Get(4) == null)
			{
				$TWITTER = new \Identity\Model\LoginType();
				$TWITTER->Name = "Twitter";
				$this->LoginTypes->Add($TWITTER);				
			}

			if($this->LoginTypes->Get(5) == null)
			{
				$Microsoft = new \Identity\Model\LoginType();
				$Microsoft->Name = "Microsoft";
				$this->LoginTypes->Add($Microsoft);				
			}

			if($this->LoginTypes->Get(6) == null)
			{
				$Joomla = new \Identity\Model\LoginType();
				$Joomla->Name = "Joomla";
				$this->LoginTypes->Add($Joomla);				
			}

			if($this->LoginTypes->Get(7) == null)
			{
				$WP = new \Identity\Model\LoginType();
				$WP->Name = "Wordpress";
				$this->LoginTypes->Add($WP);				
			}

			# Create default user roles
			if($this->Roles->Get(1) == null)
			{
				$Administrator = new \Identity\Model\Role();
				$Administrator->Name = "Administrator";
				$this->Roles->Add($Administrator);
			}

			if($this->Roles->Get(2) == null)
			{
				$Registred = new \Identity\Model\Role();
				$Registred->Name = "Registred";
				$this->Roles->Add($Registred);
			}
		}

		# Cleare expired tokens
		$this->Tokens->Where("expirydate", "<", date("Y-m-d H:i:s"))->Remove();

		$update = array();
		$obj_update = new \stdClass();
		$obj_update->Column = "lockoutend";
		$obj_update->Value = "null";
		array_push($update, $obj_update);

		$obj_update = new \stdClass();
		$obj_update->Column = "islockedout";
		$obj_update->Value = false;
		array_push($update, $obj_update);

		$this->Users->Where("lockoutend", "<", date("Y-m-d H:i:s"))->Update($update);

		# Current User
		if(\Identity\Core\Session::Get("user") != null)
		{
			$this->CurrentUser = $this->Users->Get(\Identity\Core\Session::Get("user"));
			$this->CurrentUser->Secure();		
		}
		else if(\Identity\Core\Session::GetCookie("token") != null)
		{
			$Token = $this->Tokens->Where("guid", "=", \Identity\Core\Session::GetCookie("token"))->And()->Where("expirydate", ">", date("Y-m-d H:i:s"))->Get();
			
			if($Token != null && $Token->UserId > 0)
			{
				$this->CurrentUser = $this->Users->Get($Token->UserId);
				$this->CurrentUser->Secure();
			}
		}

		if($this->CurrentUser != null)
		{
			$this->CurrentUser->Includes("Roles", $this->UserRoles);
							
			if($this->CurrentUser->Roles != null && count($this->CurrentUser->Roles) > 0)
			{
				foreach($this->CurrentUser->Roles as $UserRole)
				{
					$UserRole->Include("Role", $this->Roles);
				}
			}
		}
	}

	private function GetReplacedUserEmailText($Text, $User)
	{
		$result = $Text;

		if($User != null)
		{
			$result = str_replace("[Username]", $User->Username, $result);
			$result = str_replace("[Email]", $User->Email, $result);
			$result = str_replace("[Firstname]", $User->Firstname, $result);
			$result = str_replace("[Lastname]", $User->Lastname, $result);
			$result = str_replace("[CreatedDate]", date($this->Config->DateTimeFormatWithoutHours, strtotime($User->CreatedDate)), $result);
			$result = str_replace("[Now]", date($this->Config->DateTimeFormatWithoutSeconds, strtotime(date("Y-m-d H:i:s"))), $result);
		}
		
		return $result;
	}

	public function LoginUser($Username, $Password, $Remember = false)
	{
		$result = null;
		
		if($this->CurrentUser != null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_ALREADY_LOGGEDIN"));
		}
		else if($Username == null || empty($Username))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_USERNAME_NULL"));
		}
		else if($Password == null || empty($Password))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_PASSWORD_NULL"));
		}
		else
		{
			$user = $this->Users->Where("username", "=", $Username)->Get();
			$userFound = false;
			$userFoundByTempPassword = false;

			if($user != null && $user->Password == \Identity\Core\Security::Password($Password, $user->Salt))
			{
				$userFound = true;
			}
			else if($user != null && $user->TempPassword == \Identity\Core\Security::Password($Password, $user->Salt))
			{
				$userFound = true;
				$userFoundByTempPassword = true;
			}
			else
			{
				// Joomla Authentification
				if($this->Config->JoomlaPath != null && !empty($this->Config->JoomlaPath))
				{
					$user = $this->LoginUserByJoomla($Username, $Password);

					if($user != null && isset($user->id) && $user->id != null && !empty($user->id) && isset($user->username) && $user->username != null && !empty($user->username) && isset($user->name) && $user->name != null && !empty($user->name) && isset($user->password) && $user->password != null && !empty($user->password) && isset($user->email) && $user->email != null && !empty($user->email) && isset($user->block) && $user->block != "1")
					{
						$db_joomla_user = $this->Users->Where("username", "=", "joomla_" . $user->username)->Get();

						//create
						if($db_joomla_user == null)
						{
							$names = explode(" ", $user->name);

							$new_user = new \Identity\Model\User();
							$new_user->Username = "joomla_". $user->username;
							$new_user->ExternId = $user->id;
							$new_user->Salt = \Identity\Core\Security::Salt($this->Config->SaltLength);
							$new_user->Password = \Identity\Core\Security::Password($user->password, $new_user->Salt);							
							$new_user->LoginTypeId = 6;
							$new_user->IsActive = true;
							$new_user->Email = $user->email;
							$new_user->Firstname = ($names != null && count($names) > 0) ? $names[0] : $user->name;
							$new_user->Lastname = ($names != null && count($names) > 1) ? $names[1] : $user->name;
							$new_user->CreatedDate = date("Y-m-d H:i:s");

							if($this->Users->Where("email", "=", $new_user->Email)->Get() == null)
							{
								$new_user->Id = $this->AddUser($new_user);

								if($new_user->Id > 0)
								{
									$user = $this->Users->Get($new_user->Id);
									$userFound = true;
								}
								else
								{
									$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
								}							
							}
							else
							{
								$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_EXISTS"));
							}
						}
						else
						{
							$user = $db_joomla_user;
							$userFound = true;
						}
					}
				}
				// Wordpress Authentification
				else if($this->Config->WordPressPath != null && !empty($this->Config->WordPressPath))
				{
					$user = $this->LoginUserByWordPress($Username, $Password);
					
					if($user != null && isset($user->data) && $user->data != null && isset($user->data->ID) && $user->data->ID != null && !empty($user->data->ID) && isset($user->data->user_login) && $user->data->user_login != null && !empty($user->data->user_login) && isset($user->data->user_pass) && $user->data->user_pass != null && !empty($user->data->user_pass) && isset($user->data->user_email) && $user->data->user_email != null && !empty($user->data->user_email) && isset($user->data->display_name) && $user->data->display_name != null && !empty($user->data->display_name))
					{
						$db_wordpress_user = $this->Users->Where("username", "=", "wordpress_" . $user->data->user_login)->Get();
					
						//create
						if($db_wordpress_user == null)
						{
							$names = explode(" ", $user->data->display_name);

							$new_user = new \Identity\Model\User();
							$new_user->Username = "wordpress_". $user->data->user_login;
							$new_user->ExternId = $user->data->ID;
							$new_user->Salt = \Identity\Core\Security::Salt($this->Config->SaltLength);
							$new_user->Password = \Identity\Core\Security::Password($user->data->user_pass, $new_user->Salt);							
							$new_user->LoginTypeId = 7;
							$new_user->IsActive = true;
							$new_user->Email = $user->data->user_email;
							$new_user->Firstname = ($names != null && count($names) > 0) ? $names[0] : $user->data->display_name;
							$new_user->Lastname = ($names != null && count($names) > 1) ? $names[1] : $user->data->display_name;
							$new_user->CreatedDate = date("Y-m-d H:i:s");
							
							if($this->Users->Where("email", "=", $new_user->Email)->Get() == null)
							{
								$new_user->Id = $this->AddUser($new_user);

								if($new_user->Id > 0)
								{
									$user = $this->Users->Get($new_user->Id);
									$userFound = true;
								}
								else
								{
									$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
								}							
							}
							else
							{
								$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_EXISTS"));
							}
						}
						else
						{
							$user = $db_joomla_user;
							$userFound = true;
						}
					}
				}
			}
			
			if($userFound)
			{
				if($user->IsLockedOut)
				{
					if($user->LockoutEnd != null && !empty($user->LockoutEnd))
					{
						$msg = str_replace("[TIME]", date($this->Config->DateTimeFormatWithoutSeconds, strtotime($user->LockoutEnd)), $this->Language->Text("IF_ERROR_USER_IS_LOCKED_OUT_UNTIL"));
						
						$result = new \Exception($msg);
					}
					else
					{
						$result = new \Exception($this->Language->Text("IF_ERROR_USER_IS_LOCKED_OUT"));
					}
				}
				else if($user->IsActive)
				{
					$user->LastOnlineDate = date("Y-m-d H:i:s");
					$user->LastLoginDate = date("Y-m-d H:i:s");

					if(!$userFoundByTempPassword)
					{
						$user->TempPassword = "null";
					}

					$this->Users->Update($user);

					$this->CurrentUser = $user;
					$this->CurrentUser->Secure();
					$this->CurrentUser->Includes("Roles", $this->UserRoles);					
									
					if($this->CurrentUser->Roles != null && count($this->CurrentUser->Roles) > 0)
					{
						foreach($this->CurrentUser->Roles as $UserRole)
						{
							$UserRole->Include("Role", $this->Roles);
						}
					}

					$result = $this->CurrentUser;

					$this->Tokens->Where("userid", "=", $this->CurrentUser->Id)->Remove();
	
					if($Remember)
					{
						$Token = new \Identity\Model\Token();
						$Token->UserId = $user->Id;
						$Token->Guid = \Identity\Core\Security::Salt($this->Config->SaltLength);
						$Token->CreatedDate = date("Y-m-d H:i:s");
						$Token->ExpiryDate = date("Y-m-d H:i:s", strtotime($Token->CreatedDate ."+" . $this->Config->LoginRememberDays . "days"));
						
						if($this->Tokens->Add($Token) > 0)
						{
							\Identity\Core\Session::AddCookie("token", $Token->Guid, $Token->ExpiryDate);
						}
					}
	
					\Identity\Core\Session::Add("user", $user->Id);
				}
				else
				{
					$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_ACTIVATED"));
				}
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
			}
		}
		
		return $result;
	}

	public function LoginUserByJoomla($Username, $Password)
	{
		$result = null;
		
		if(!defined("_JEXEC")) { define("_JEXEC", 1); }

		if($this->Config->JoomlaPath != null && !empty($this->Config->JoomlaPath))
		{
			if (!defined("JPATH_BASE"))
			{
				define("JPATH_BASE", $this->Config->JoomlaPath);
			}
			
			if(file_exists(JPATH_BASE . DS . "includes" . DS . "defines.php") && file_exists(JPATH_BASE . DS . "includes" . DS . "framework.php"))
			{
				require_once(JPATH_BASE . DS . "includes" . DS . "defines.php");
				require_once(JPATH_BASE . DS . "includes" . DS . "framework.php");			
				
				$credentials['username'] = $Username;
				$credentials['password'] = $Password;
	
				$db    = \JFactory::getDbo();
				$query = $db->getQuery(true)->select('id, password')->from('#__users')->where('username=' . $db->quote($credentials['username']));
	
				$db->setQuery($query);
				$db_result = $db->loadObject();
				
				if($db_result)
				{
					$match = \JUserHelper::verifyPassword($credentials['password'], $db_result->password, $db_result->id);
					
					if($match === true)
					{
						$result = \JUser::getInstance($db_result->id);
					}
				}
			}
		}

		return $result;
	}

	public function LoginUserByWordPress($Username, $Password)
	{
		$result = null;
		
		if($this->Config->WordPressPath != null && !empty($this->Config->WordPressPath))
		{
			if(file_exists($this->Config->WordPressPath . DS . "wp-load.php"))
			{
				require_once($this->Config->WordPressPath . DS . "wp-load.php");

				$user = get_user_by('login', $Username);

				if($user != null && wp_check_password($Password, $user->data->user_pass, $user->ID))
				{
					$result = $user;
				}
			}
		}
		
		return $result;
	}

	public function Logout()
	{
		$this->CurrentUser = null;
		\Identity\Core\Session::ClearAll();
	}

	public function ResendActivationCode($Email)
	{
		$result = null;

		if($this->CurrentUser != null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_ALREADY_LOGGEDIN"));
		}
		else if($Email == null || empty($Email))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_NULL"));
		}
		else
		{
			$User = $this->Users->Where("email", "=", $Email)->And()->Where("isactive", "=", false)->And()->Where("activationcode", "<>", "null")->Get();

			if($User != null)
			{
				if($this->Config->MailSendUserRegistration && $this->Config->MailSendUserActivationSubject != null && $this->Config->MailSendUserActivationContent != null && !empty($this->Config->MailSendUserActivationContent) && !empty($this->Config->MailSendUserActivationSubject) && $this->Config->EmailFrom != null && $this->Config->EmailFromName != null && !empty($this->Config->EmailFrom) && !empty($this->Config->EmailFromName))
				{
					$result = true;

					$subject = $this->GetReplacedUserEmailText($this->Config->MailSendUserActivationSubject, $User);
					$subject = str_replace("[CODE]", $User->ActivationCode, $subject);

					$msg = $this->GetReplacedUserEmailText($this->Config->MailSendUserRegistrationContent, $User);
					$msg = str_replace("[CODE]", $User->ActivationCode, $msg);

					\Identity\Core\Mail::Send($this->Config->EmailFrom, $this->Config->EmailFromName, $User->Email, $subject, $msg, $this->Config->MailSendUserActivationIsHTML, null, $this->Config->EmailReplayTo);
				}
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_NOT_FOUND"));
			}
		}

		return $result;
	}

	public function ActivateCode($Code)
	{
		$result = null;

		if($this->CurrentUser != null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_ALREADY_LOGGEDIN"));
		}
		else if($Code == null || empty($Code))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_ACTIVATION_CODE_NULL"));
		}
		else
		{
			$User = $this->Users->Where("activationcode", "=", $Code)->Get();

			if($User != null)
			{
				if($User->IsActive)
				{
					$result = new \Exception($this->Language->Text("IF_ERROR_USER_ALREADY_ACTIVATED"));
				}
				else
				{
					$User->ActivationCode = "null";
					$User->IsActive = true;
					$User->EmailConfirmed = true;
					
					if($this->Users->Update($User))
					{
						$result = true;

						if($this->Config->MailSendUserRegistration && $this->Config->MailSendUserRegistrationSubject != null && $this->Config->MailSendUserRegistrationContent != null && !empty($this->Config->MailSendUserRegistrationContent) && !empty($this->Config->MailSendUserRegistrationSubject) && $this->Config->EmailFrom != null && $this->Config->EmailFromName != null && !empty($this->Config->EmailFrom) && !empty($this->Config->EmailFromName))
						{
							$subject = $this->GetReplacedUserEmailText($this->Config->MailSendUserRegistrationSubject, $User);
							$msg = $this->GetReplacedUserEmailText($this->Config->MailSendUserRegistrationContent, $User);

							\Identity\Core\Mail::Send($this->Config->EmailFrom, $this->Config->EmailFromName, $User->Email, $subject, $msg, $this->Config->MailSendUserRegistrationIsHTML, null, $this->Config->EmailReplayTo);
						}
					}
					else
					{
						$result = new \Exception($this->Language->Text("IF_ERROR_USER_COULD_NOT_BE_ACTIVATED"));
					}
				}
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_ACTIVATION_CODE_NOT_FOUND"));
			}
		}

		return $result;
	}

	public function ActivateUser($User, $NeededRole = null)
	{
		$result = false;
	
		if($this->CurrentUser != null)
		{
			if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
			}
			else if($User != null && $User->Id > 0)
			{
				$DbUser =$this->Users->Get($User->Id);

				if($DbUser != null)
				{
					$DbUser->ModifiedBy = $this->CurrentUser->Id;
					$DbUser->ModifiedDate = date("Y-m-d H:i:s");
					$DbUser->IsActive = true;
					$DbUser->IsLockedOut = false;
					$DbUser->LockoutEnd = "null";

					$result = $this->Users->Update($DbUser);
				}
			}
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}

		return $result;
	}

	public function PasswordForgot($Email)
	{
		$result = null;

		if($this->CurrentUser != null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_ALREADY_LOGGEDIN"));
		}
		else if($Email == null || empty($Email))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_NULL"));
		}
		else
		{
			$User = $this->Users->Where("email", "=", $Email)->Get();

			if($User != null)
			{
				$TempPassword = \Identity\Core\Security::Salt(16);
				$User->TempPassword = \Identity\Core\Security::Password($TempPassword, $User->Salt);
				
				if($this->Users->Update($User))
				{
					if($this->Config->MailSendPasswordForgotSubject != null && $this->Config->MailSendPasswordForgotContent != null && !empty($this->Config->MailSendPasswordForgotContent) && !empty($this->Config->MailSendPasswordForgotSubject) && $this->Config->EmailFrom != null && $this->Config->EmailFromName != null && !empty($this->Config->EmailFrom) && !empty($this->Config->EmailFromName))
					{
						$result = true;

						$subject = $this->GetReplacedUserEmailText($this->Config->MailSendPasswordForgotSubject, $User);
						$subject = str_replace("[PASSWORD]", $TempPassword, $subject);
						
						$msg = $this->GetReplacedUserEmailText($this->Config->MailSendPasswordForgotContent, $User);
						$msg = str_replace("[PASSWORD]", $TempPassword, $msg);

						\Identity\Core\Mail::Send($this->Config->EmailFrom, $this->Config->EmailFromName, $User->Email, $subject, $msg, $this->Config->MailSendPasswordForgotIsHTML, null, $this->Config->EmailReplayTo);
					}
				}
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_NOT_FOUND"));
			}
		}

		return $result;
	}

	public function PasswordReset($NewPassword, $ConfirmPassword, $OldPassword, $User)
	{
		$result = null;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($User == null || $User->Id <= 0)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
		}
		else if($NewPassword == null && empty($NewPassword))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_PASSWORD_NULL"));
		}
		else if($NewPassword != $ConfirmPassword)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_PASSWORD_NOT_CONFIRMED"));
		}
		else if($OldPassword == null || empty($OldPassword))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_PASSWORD_NULL"));
		}
		else if(strlen($NewPassword) < $this->Config->PasswordMinimumLength)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_PASSWORD_LENGTH"));
		}
		else
		{
			$DbUser =$this->Users->Get($User->Id);
	
			if($DbUser != null)
			{
				if(($DbUser->Password == \Identity\Core\Security::Password($OldPassword, $DbUser->Salt)) || ($DbUser->TempPassword == \Identity\Core\Security::Password($OldPassword, $DbUser->Salt)))
				{
					$DbUser->TempPassword = "null";
					$DbUser->Salt = \Identity\Core\Security::Salt($this->Config->SaltLength);
					$DbUser->Password = \Identity\Core\Security::Password($NewPassword, $DbUser->Salt);
					$DbUser->ModifiedBy = $this->CurrentUser->Id;
					$DbUser->ModifiedDate = date("Y-m-d H:i:s");

					if($this->Users->Update($DbUser))
					{
						$this->CurrentUser = $DbUser;
						$result = true;
						
						if($this->Config->MailSendPasswordResetSubject != null && $this->Config->MailSendPasswordResetContent != null && !empty($this->Config->MailSendPasswordResetContent) && !empty($this->Config->MailSendPasswordResetSubject) && $this->Config->EmailFrom != null && $this->Config->EmailFromName != null && !empty($this->Config->EmailFrom) && !empty($this->Config->EmailFromName))
						{
							$result = true;

							$subject = $this->GetReplacedUserEmailText($this->Config->MailSendPasswordResetSubject, $User);							
							$msg = $this->GetReplacedUserEmailText($this->Config->MailSendPasswordResetContent, $User);

							\Identity\Core\Mail::Send($this->Config->EmailFrom, $this->Config->EmailFromName, $User->Email, $subject, $msg, $this->Config->MailSendPasswordForgotIsHTML, null, $this->Config->EmailReplayTo);
						}
					}
				}
				else
				{
					$result = new \Exception($this->Language->Text("IF_ERROR_USER_TEMP_PASSWORD_INVALID"));
				}
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
			}
		}

		return $result;
	}
	
	# User
	public function UpdateUser($User, $NeededRole = null)
	{
		$result = false;
		
		if($this->CurrentUser != null)
		{
			if($User == null || $User->Id <= 0)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
			}
			else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
			}
			else if($User->Email == null || empty($User->Email))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_NULL"));
			}
			else if($User->Firstname == null || empty($User->Firstname))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_FIRSTNAME_NULL"));
			}
			else if($User->Lastname == null || empty($User->Lastname))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_LASTNAME_NULL"));
			}
			else if(!filter_var($User->Email, FILTER_VALIDATE_EMAIL))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_INVALID"));
			}
			else if($this->Users->Where("email", "=", $User->Email)->And()->Where("id", "<>", $User->Id)->Get() != null)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_EXISTS"));
			}
			else
			{
				$DbUser =$this->Users->Get($User->Id);

				if($DbUser != null)
				{
					$DbUser->ModifiedBy = $this->CurrentUser->Id;
					$DbUser->ModifiedDate = date("Y-m-d H:i:s");
					$DbUser->IsActive = $User->IsActive;
					$DbUser->IsLockedOut = $User->IsLockedOut;
					$DbUser->LockoutEnd = $User->LockoutEnd;
					$DbUser->Email = $User->Email;
					$DbUser->Firstname = $User->Firstname;
					$DbUser->Lastname = $User->Lastname;
					$DbUser->Picture = $User->Picture;
	
					$result = $this->Users->Update($DbUser);
				}
			}	
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}

		return $result;
	}

	public function AddUser($User, $NeededRole = null)
	{
		$result = null;

		if($User != null)
		{
			if($this->CurrentUser != null && $NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
			}
			else if($User->Username == null || empty($User->Username))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_USERNAME_NULL"));
			}
			else if($User->Password == null || empty($User->Password))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_PASSWORD_NULL"));
			}
			else if($User->Email == null || empty($User->Email))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_NULL"));
			}
			else if($User->Firstname == null || empty($User->Firstname))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_FIRSTNAME_NULL"));
			}
			else if($User->Lastname == null || empty($User->Lastname))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_LASTNAME_NULL"));
			}
			else if($User->LoginTypeId == null || empty($User->LoginTypeId))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_LOGINTYPE_NULL"));
			}
			else if(strlen($User->Password) < $this->Config->PasswordMinimumLength)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_PASSWORD_LENGTH"));
			}
			else if(strlen($User->Username) < $this->Config->UsernameMinimumLength)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_USERNAME_LENGTH"));
			}
			else if(!filter_var($User->Email, FILTER_VALIDATE_EMAIL))
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_INVALID"));
			}
			else if($this->LoginTypes->Get($User->LoginTypeId) == null)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_LOGINTYPE_INVALID"));
			}
			else if($this->Users->Where("email", "=", $User->Email)->Get() != null)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_EMAIL_EXISTS"));
			}
			else if($this->Users->Where("username", "=", $User->Username)->Get() != null)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_USERNAME_EXISTS"));
			}
			else
			{
				$User->Salt = \Identity\Core\Security::Salt($this->Config->SaltLength);
				$User->Password = \Identity\Core\Security::Password($User->Password, $User->Salt);

				if($this->Config->UserRegistrationHasToActivate)
				{
					$User->IsActive = false;

					if($this->Config->MailSendUserRegistration && $this->Config->MailSendUserActivationSubject != null && $this->Config->MailSendUserActivationContent != null && !empty($this->Config->MailSendUserActivationContent) && !empty($this->Config->MailSendUserActivationSubject) && $this->Config->EmailFrom != null && $this->Config->EmailFromName != null && !empty($this->Config->EmailFrom) && !empty($this->Config->EmailFromName))
					{
						$User->ActivationCode = \Identity\Core\Security::Salt($this->Config->SaltLength);

						\Identity\Core\Mail::Send($this->Config->EmailFrom, $this->Config->EmailFromName, $User->Email, $this->Config->MailSendUserActivationSubject, str_replace("[CODE]", $User->ActivationCode, $this->Config->MailSendUserActivationContent), $this->Config->MailSendUserActivationIsHTML, null, $this->Config->EmailReplayTo);
					}
				}

				$result = $this->Users->Add($User);

				if($result == null)
				{
					$validations = $this->Users->Validate($User);

					if($validations != null && count($validations) > 0)
					{
						$msg = $this->Language->Text("IF_ERROR_USER_INVALID"). "\r\n";

						foreach($validations as $validation)
						{
							$msg = $msg . $validation->Error . "\r\n";
						}

						return new \Exception($msg);
					}
				}
				else
				{
					if(!$this->Config->UserRegistrationHasToActivate && $this->Config->MailSendUserRegistration && $this->Config->MailSendUserRegistrationSubject != null && $this->Config->MailSendUserRegistrationContent != null && !empty($this->Config->MailSendUserRegistrationContent) && !empty($this->Config->MailSendUserRegistrationSubject) && $this->Config->EmailFrom != null && $this->Config->EmailFromName != null && !empty($this->Config->EmailFrom) && !empty($this->Config->EmailFromName))
					{
						\Identity\Core\Mail::Send($this->Config->EmailFrom, $this->Config->EmailFromName, $User->Email, $this->Config->MailSendUserRegistrationSubject, $this->Config->MailSendUserRegistrationContent, $this->Config->MailSendUserRegistrationIsHTML, null, $this->Config->EmailReplayTo);
					}
				}
			}
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NULL"));
		}

		return $result;
	}

	public function DeleteUser($User, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser != null)
		{
			if($User == null || $User->Id <= 0)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
			}
			else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
			}
			else
			{
				$this->UserRoles->Where("userid", "=", $User->Id)->Remove();

				$update = array();
				$obj_update = new \stdClass();
				$obj_update->Column = "modifiedby";
				$obj_update->Value = "null";
				array_push($update, $obj_update);

				$obj_update = new \stdClass();
				$obj_update->Column = "createdby";
				$obj_update->Value = "null";
				array_push($update, $obj_update);

				$obj_update = new \stdClass();
				$obj_update->Column = "profileid";
				$obj_update->Value = "null";
				array_push($update, $obj_update);
				$this->Users->Where("id", "=", $User->Id)->Update($update);

				$this->Tokens->Where("userid", "=", $User->Id)->Remove();	
				
				if($User->ProfileId != null && $User->ProfileId > 0)
				{
					$this->Phones->Where("profileid", "=", $User->ProfileId)->Remove();
					$this->Addresses->Where("profileid", "=", $User->ProfileId)->Remove();
					$this->Profiles->Where("id", "=", $User->ProfileId)->Remove();
				}

				$result = $this->Users->Remove($User);
			}
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}

		return $result;
	}

	# User Profile
	public function AddProfile($User, $Profile, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($User != null && $Profile != null)
		{
			if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
			}
			else if($User->ProfileId > 0 && $this->Profiles->Get($User->ProfileId) != null)
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_USER_ALREADY_HAS_PROFILE"));
			}
			else
			{
				$DbUser = $this->Users->Get($User->Id);

				if($DbUser != null)
				{
					$Profile->CreatedBy = $this->CurrentUser->Id;
					$DbUser->ProfileId = $this->Profiles->Add($Profile);
					$this->Users->Update($DbUser);

					$result = ($DbUser->ProfileId > 0);
				}
				else
				{
					$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
				}
			}
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
		}

		return $result;
	}

	public function UpdateProfile($User, $Profile, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		elseif($Profile != null && $Profile->Id > 0)
		{
			$DbProfile = $this->Profiles->Get($Profile->Id);

			if($DbProfile != null)
			{
				$DbProfile->Title = $Profile->Title;
				$DbProfile->ModifiedBy = $this->CurrentUser->Id;
				$DbProfile->ModifiedDate = date("Y-m-d H:i:s");
				$result = $this->Profiles->Update($DbProfile);
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_PROFILE_NOT_FOUND"));
			}
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_PROFILE_NOT_FOUND"));
		}

		return $result;
	}

	public function DeleteProfile($User, $Profile, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		else if($Profile != null && $Profile->Id > 0)
		{
			$update = array();
			$obj_update = new \stdClass();
			$obj_update->Column = "profileid";
			$obj_update->Value = "null";
			array_push($update, $obj_update);
			$this->Users->Where("id", "=", $User->Id)->Update($update);

			$this->Addresses->Where("profileid", "=", $Profile->Id)->Remove();
			$this->Phones->Where("profileid", "=", $Profile->Id)->Remove();

			$result = $this->Profiles->Where("id", "=", $Profile->Id)->Remove();
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_PROFILE_NOT_FOUND"));
		}

		return $result;
	}

	# User Profile Address
	public function AddAddress($User, $Profile, $Address, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		else if($User != null && $Profile != null && $Address != null)
		{
			$Address->ProfileId = $Profile->Id;
			$Address->CreatedBy = $this->CurrentUser->Id;
			$Address->Id = $this->Addresses->Add($Address);

			$result = ($Address->Id > 0);
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_PROFILE_NOT_FOUND"));
		}

		return $result;
	}

	public function UpdateAddress($User, $Address, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		elseif($Address != null && $Address->Id > 0)
		{
			$DbAddress = $this->Addresses->Get($Address->Id);

			if($DbAddress != null)
			{
				$DbAddress->Country = $Address->Country;
				$DbAddress->City = $Address->City;
				$DbAddress->ZIP = $Address->ZIP;
				$DbAddress->Street = $Address->Street;
				$DbAddress->HomeNr = $Address->HomeNr;
				$DbAddress->Addition = $Address->Addition;
				$DbAddress->IsPrimary = $Address->IsPrimary;
				$DbAddress->ForBilling = $Address->ForBilling;
				$DbAddress->ForDelivery = $Address->ForDelivery;
				$DbAddress->ModifiedBy = $this->CurrentUser->Id;
				$DbAddress->ModifiedDate = date("Y-m-d H:i:s");
				$result = $this->Addresses->Update($DbAddress);
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_ADDRESS_NOT_FOUND"));
			}
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_ADDRESS_NOT_FOUND"));
		}

		return $result;
	}

	public function DeleteAddress($User, $Address, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		else if($Address != null && $Address->Id > 0)
		{
			$result = $this->Addresses->Where("id", "=", $Address->Id)->Remove();
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_ADDRESS_NOT_FOUND"));
		}

		return $result;
	}

	# User Profile Phone
	public function AddPhone($User, $Profile, $Phone, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		else if($User != null && $Profile != null && $Phone != null)
		{
			$Phone->ProfileId = $Profile->Id;
			$Phone->CreatedBy = $this->CurrentUser->Id;
			$Phone->Id = $this->Phones->Add($Phone);

			$result = ($Phone->Id > 0);
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_PROFILE_NOT_FOUND"));
		}

		return $result;
	}

	public function UpdatePhone($User, $Phone, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		elseif($Phone != null && $Phone->Id > 0)
		{
			$DbPhone = $this->Phones->Get($Phone->Id);

			if($DbPhone != null)
			{
				$DbPhone->Title = $Phone->Title;
				$DbPhone->Number = $Phone->Number;
				$DbPhone->IsPrimary = $Phone->IsPrimary;
				$DbPhone->IsLandline = $Phone->IsLandline;
				$DbPhone->IsOffice = $Phone->IsOffice;
				$DbPhone->IsPrivate = $Phone->IsPrivate;
				$DbPhone->IsMobile = $Phone->IsMobile;
				$DbPhone->ModifiedBy = $this->CurrentUser->Id;
				$DbPhone->ModifiedDate = date("Y-m-d H:i:s");
				$result = $this->Phones->Update($DbPhone);
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_PHONE_NOT_FOUND"));
			}
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_PHONE_NOT_FOUND"));
		}

		return $result;
	}

	public function DeletePhone($User, $Phone, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		else if($Phone != null && $Phone->Id > 0)
		{
			$result = $this->Phones->Where("id", "=", $Phone->Id)->Remove();
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_PHONE_NOT_FOUND"));
		}

		return $result;
	}

	# Role
	public function AddRole($Role, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		else if($this->Roles->Where("name", "LIKE", $Role->Name)->Get() != null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_ROLE_ALREADY_EXISTS"));
		}
		else if($Role != null)
		{
			$Role->CreatedBy = $this->CurrentUser->Id;
			$Role->Id = $this->Roles->Add($Role);

			$result = ($Role->Id > 0);
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_ROLE_NOT_FOUND"));
		}

		return $result;
	}

	public function UpdateRole($Role, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		elseif($Role != null && $Role->Id > 0)
		{
			$DbRole = $this->Roles->Get($Role->Id);

			if($DbRole != null)
			{
				$DbRole->Name = $Role->Name;
				$DbRole->ModifiedBy = $this->CurrentUser->Id;
				$DbRole->ModifiedDate = date("Y-m-d H:i:s");
				$result = $this->Roles->Update($DbRole);
			}
			else
			{
				$result = new \Exception($this->Language->Text("IF_ERROR_ROLE_NOT_FOUND"));
			}
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_ROLE_NOT_FOUND"));
		}

		return $result;
	}

	public function DeleteRole($Role, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole) && $User->Id != $this->CurrentUser->Id)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		elseif($Role != null && $Role->Id > 0)
		{
			$this->UserRoles->Where("roleid", "=", $Role->Id)->Remove();
			$result = $this->Roles->Remove($Role);
		}
		else
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_ROLE_NOT_FOUND"));
		}

		return $result;
	}

	# User Role
	public function AddUserRole($User, $Role, $NeededRole = null)
	{
		$result = false;

		if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		else if($User == null || $User->Id < 0)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
		}
		else if($Role == null || $Role->Id < 0)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_ROLE_NOT_FOUND"));
		}
		else if($this->UserRoles->Where("userid", "=", $User->Id)->And()->Where("roleid", "=", $Role->Id)->Get() != null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USERROLE_ALREADY_EXISTS"));
		}
		else
		{
			$UserRole = new \Identity\Model\UserRole();
			$UserRole->UserId = $User->Id;
			$UserRole->RoleId = $Role->Id;
			$UserRole->Role = $Role;
			$this->UserRoles->Add($UserRole);

			$result = true;
		}

		return $result;
	}

	public function DeleteUserRole($User, $Role, $NeededRole = null)
	{
		$result = false;

		if($this->CurrentUser == null)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_LOGGEDIN"));
		}
		else if($NeededRole != null && !empty($NeededRole) && !$this->CurrentUser->IsInRole($NeededRole))
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NO_PERMISSION"));
		}
		else if($User == null || $User->Id < 0)
		{
			$result = new \Exception($this->Language->Text("IF_ERROR_USER_NOT_FOUND"));
		}
		else if($Role == null || $Role->Id < 0)
		{
			$result = new \Exception($this->Language->Text("ERROR_ROLE_NOT_FOUND"));
		}
		else
		{
			$result = $this->UserRoles->Where("userid", "=", $User->Id)->And()->Where("roleid", "=", $Role->Id)->Remove();
		}

		return $result;
	}
}

?>	