<?php
namespace Rules\Facilities;
use Rules\IRule;

/**
 * A constructable facility
 * @author Jan "Teyras" Buchar
 */
interface IFacility extends IRule
{
	/**
	 * Get this building's dependencies
	 * @return array of $facility => $level
	 */
	public function getDependencies ();

	/**
	 * Get the construction cost of this building or its upgrade to given level
	 * @param int
	 * @return array of $resource => $cost
	 */
	public function getConstructionCost ($level = 1);

	/**
	 * Get the construction time of this building or its upgrade to given level
	 * @param int
	 * @return int
	 */
	public function getConstructionTime ($level = 1);

	/**
	 * Get the demolition cost of this building or its downgrade to given level
	 * @param int
	 * @param int
	 * @return array of $resource => $cost
	 */
	public function getDemolitionCost ($from, $level = 0);

	/**
	 * Get the demolition time of this building or its downgrade to given level
	 * @param int
	 * @param int
	 * @return int
	 */
	public function getDemolitionTime ($from, $level = 0);

	/**
	 * Get the raw production of the building (before applying any bonuses)
	 * @param int
	 * @return array of $resource => $production
	 */
	public function getProduction ($level = 1);

	/**
	 *	Percentual bonus to the defence
	 *  @return float
	 */
	public function getDefenceBonus ();
}
