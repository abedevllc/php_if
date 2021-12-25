<?php

namespace Identity\Model;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class Phone
{
	/** pk, auto_increment, type: int, length: 11 */
	public $Id;
	
	/** type: int, length: 11, not null, fk, fk_table: profile, fk_column: id */
	public $ProfileId;
	
	/** type: varchar, length: 255, null */
	public $Title;
		
	/** type: varchar, length: 50, null */
	public $Number;
	
	/** type: bit, length:1, not null, default: 0 */
	public $IsPrimary;
	
	/** type: bit, length:1, not null, default: 0 */
	public $IsLandline;
	
	/** type: bit, length:1, not null, default: 0 */
	public $IsOffice;
	
	/** type: bit, length:1, not null, default: 0 */
	public $IsPrivate;
	
	/** type: bit, length:1, not null, default: 0 */
	public $IsMobile;
	
	/** type: datetime, not null, default: current_timestamp */
	public $CreatedDate;
	
	/** type: int, length: 11, not null, fk, fk_table: user, fk_column: id */
	public $CreatedBy;
	
	/** type: datetime, null*/
	public $ModifiedDate;
	
	/** type: int, length: 11, null, fk, fk_table: user, fk_column: id */
	public $ModifiedBy;
}

?>