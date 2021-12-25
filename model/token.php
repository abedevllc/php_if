<?php

namespace Identity\Model;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class Token
{
	/** pk, auto_increment, type: int, length: 11 */	
	public $Id;
	
	/** type: int, length: 11, not null, fk, fk_table: user, fk_column: id */
	public $UserId;
	
	/** type: varchar, length: 50, not null */
	public $Guid;	
	
	/** type: datetime, not null, default: current_timestamp */
	public $CreatedDate;
	
	/** type: datetime, not null */
	public $ExpiryDate;	
}

?>