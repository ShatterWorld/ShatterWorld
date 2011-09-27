<?php
namespace Rules\Facilities;

/**
 * A facility with construction-based production
 * @author Jan "Teyras" Buchar
 */
interface IConstructionFacility extends IFacility
{
	/**
	 * Get the total construction capacity of the building on given level
	 * @param int
	 * @return int
	 */
	public function getCapacity ($level = 1);
}