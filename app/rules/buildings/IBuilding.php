<?php
namespace Rules\Buildings;

interface IBuilding extends IRule
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
}