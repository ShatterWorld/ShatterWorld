<?php
namespace Rules\Facilities;
use Rules\IRule;

interface IFacility extends IRule
{
	/**
	 * Get this building's dependencies
	 * @return array
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
	 * Get the raw production of the building (before applying any bonuses)
	 * @param int
	 * @return array of $resource => $production
	 */
	public function getProduction ($level = 1);
}