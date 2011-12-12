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
	 * Get further description of the research
	 * @return void
	 */
	public function getExplanation ();

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
	 * Get the conflicts of this research
	 * @return array of $research => $level
	 */
	public function getConflicts();

	/**
	 * Anything that should be done after finishing a research
	 * @param Entities\Construction
	 * @return void
	 */
	public function afterResearch (Entities\Construction $construction);

	/**
	 * Returns the maximum level
	 * @return int
	 */
	public function getLevelCap ();

	/**
	 * Returns the category of the research
	 * @return string
	 */
	public function getCategory ();


}
