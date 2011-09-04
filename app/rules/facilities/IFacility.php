<?php
namespace Rules\Facilities;

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
	public function getCost ($level = 1);
	
	/**
	 * Get the raw production of the building (before applying any bonuses)
	 * @param int
	 * @return array of $resource => $production
	 */
	public function getProduction ($level = 1);
}