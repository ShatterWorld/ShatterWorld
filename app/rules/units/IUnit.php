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
	
	/**
	 * Get the amount of each resource the unit is able to carry
	 * @return int
	 */
	public function getCapacity ();
	
	/**
	 * Get the training cost of the unit
	 * @return array of $resource => $cost
	 */
	public function getCost ();
	
	/**
	 * Get the upkeep cost of the unit per second
	 * @return array of $resource => $cost
	 */
	public function getUpkeep ();
	
	/**
	 * Get the time required to train the unit
	 * @return int
	 */
	public function getTrainingTime ();
	
	/**
	 * Determines how difficult it is to produce the unit (how many slots it takes)
	 * @return array of $slot => $amount
	 */
	public function getDifficulty ();
}