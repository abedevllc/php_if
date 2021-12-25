<?php

namespace Identity\Model;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class User
{
	/** pk, auto_increment, type: int, length: 11 */
	public $Id;
	
	/** type: varchar, length: 50, null */
	public $ExternId;
	
	/** type: int, length: 10, not null, default: 1, fk, fk_table: logintype, fk_column: id */
	public $LoginTypeId;

	/** ignore */
	public $LoginType;
	
	/** type: varchar, length: 50, not null */
	public $Username;
	
	/** type: varchar, length: 100, not null */
	public $Password;
	
	/** type: varchar, length: 100, null */
	public $TempPassword;

	/** type: varchar, length: 100, not null */
	public $Salt;
	
	/** type: varchar, length: 50, not null, unique */
	public $Email;
	
	/** type: bit, length:1, not null, default: 0 */
	public $EmailConfirmed;
	
	/** type: varchar, length: 250, not null */
	public $Firstname;
		
	/** type: varchar, length: 250, not null */
	public $Lastname;
		
	/** type: bit, length:1, not null, default: 1 */
	public $IsActive;

	/** tpye: varchar, length:100, null */
	public $ActivationCode;

	/** type: bit, length:1, not null, default: 0 */
	public $IsLockedOut;
	
	/** type: datetime, null */
	public $LockoutEnd;
	
	/** type: datetime, null */
	public $LastLoginDate;
	
	/** type: datetime, null */
	public $LastOnlineDate;
	
	/** type: datetime, not null, default: current_timestamp */
	public $CreatedDate;
	
	/** type: int, length: 11, null, fk, fk_table: user, fk_column: id */
	public $CreatedBy;
	
	/** type: datetime, null */
	public $ModifiedDate;
	
	/** type: int, length: 11, null, fk, fk_table: user, fk_column: id */
	public $ModifiedBy;
	
	/** ignore */
	public $Profile;
	
	/** type: int, length: 11, null, fk, fk_table: profile, fk_column: id */
	public $ProfileId;
	
	/** ignore */
	public $Roles;
	
	/** ignore */
	public $Tokens;

	/** type: longblob, null, form_type: file */
	public $Picture;

	/** Removes password properties */
	public function Secure()
	{
		unset($this->Salt);
		unset($this->Password);
		unset($this->TempPassword);
	}

	/** Checks if user has a role */
	public function IsInRole($Role)
	{
		$result = false;

		if($this->Roles != null && $Role != null && !empty($Role))
		{
			foreach($this->Roles as $R)
			{
				if($R != null && $R->Role != null && $R->Role->Name != null && !empty($R->Role->Name) && strtolower($R->Role->Name) == strtolower($Role))
				{
					$result = true;
					break;
				}
			}
		}

		return $result;
	}
}

?>