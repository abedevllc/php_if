<?php

namespace Identity\Model;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class Profile
{
	/** pk, auto_increment, type: int, length: 11 */
	public $Id;
	
	/** type: varchar, length: 255, null */
	public $Title;

	/** type: datetime, null */
	public $BirthDay;

	/** type: varchar, length: 255, null */
	public $Web;

	/** ignore */
	public $Addresses;
	
	/** ignore */
	public $Phones;
	
	/** type: datetime, default: current_timestamp, not null*/
	public $CreatedDate;
	
	/** type: int, length: 11, not null, fk, fk_table: user, fk_column: id */
	public $CreatedBy;
	
	/** type: datetime, null*/
	public $ModifiedDate;
	
	/** type: int, length: 11, null, fk, fk_table: user, fk_column: id */
	public $ModifiedBy;
}

?>