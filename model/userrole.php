<?php

namespace Identity\Model;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class UserRole
{
	/** pk, fk, fk_table: user, fk_column: id, type: int, length: 11, not null */	
	public $UserId;
	
	/** pk, fk, fk_table: role, fk_column: id, type: int, length: 11, not null */	
    public $RoleId;	
    
    /** ignore */
    public $Role;
}

?>