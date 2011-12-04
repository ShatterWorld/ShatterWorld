<?php
namespace Rules\Quests;
use Rules\IRule;
use Entities;

/**
 * A quest
 * @author Jan "Teyras" Buchar
 */
interface IQuest extends IRule
{
	/**
	 * Get quests the player has to complete before being able to start this quest
	 * @return array
	 */
	public function getDependencies ();
	
	/**
	 * Verify if the conditions for completing this quest have been satisfied
	 * @return bool
	 */
	public function isCompleted (Entities\Quest $quest);
	
	/**
	 * Reward a clan for completing this quest
	 * @param Entities\Clan
	 * @return void
	 */
	public function processRewards (Entities\Clan $clan);
	
	/**
	 * Get the score value of this quest
	 * @return int
	 */
	public function getValue ();
}