<?php
namespace Rules\Units;
use Rules\IRule;

/**
 * A military unit rule
 * @author Jan "Teyras" Buchar
 */
interface IUnit extends IRule
{
	/**
	 * Get unit's base attack power
	 * @return int
	 */
	public function getAttack ();
	
	/**
	 * Get unit's base defense power
	 * @return int
	 */
	public function getDefense ();
	
	/**
	 * Get unit's base speed (seconds to pass through one field)
	 * @return int
	 */
	public function getSpeed ();
}