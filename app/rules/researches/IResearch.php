<?php
namespace Rules\Researches;
use Rules\IRule;
use Entities;

/**
 * A research
 * @author Jan "Teyras" Buchar
 */
interface IResearch extends IRule
{
	/**
	 * Get the base cost of this research on given level
	 * @param int
	 * @return array of $resource => $cost
	 */
	public function getCost ($level = 1);
	
	/**
	 * Get the time this research takes on given level
	 * @param int
	 * @return int
	 */
	public function getResearchTime ($level = 1);
	
	/**
	 * Get the dependencies of this research
	 * @return array of $research => $level
	 */
	public function getDependencies ();
	
	/**
	 * Anything that should be done after finishing a research
	 * @param Entities\Construction
	 * @return void
	 */
	public function afterResearch (Entities\Construction $construction);
}