<?php

namespace Identity\Model;

if(!defined("IdentityFramework")){ die("Access Denied!"); }

class Address
{
	/** pk, auto_increment, type: int, length: 11 */
	public $Id;
	
	/** type: int, length: 11, not null, fk, fk_table: profile, fk_column: id */
	public $ProfileId;
	
	/** type: varchar, length: 100, not null */
	public $Country;
		
	/** type: varchar, length: 100, not null */
	public $City;
		
	/** type: varchar, length: 100, not null */
	public $ZIP;
	
	/** type: varchar, length: 100, not null */
	public $Street;
		
	/** type: varchar, length: 100, not null */
	public $HomeNr;
		
	/** type: varchar, length: 100, null */
	public $Addition;
		
	/** type: bit, length:1, not null, default: 0 */
	public $IsPrimary;
	
	/** type: bit, length:1, not null, default: 0 */
	public $ForBilling;
	
	/** type: bit, length:1, not null, default: 0 */
	public $ForDelivery;
	
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