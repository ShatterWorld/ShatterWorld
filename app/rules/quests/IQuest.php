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
	 * Get further explanation of quest
	 * @return string
	 */
	public function getExplanation ($level = 1);

	/**
	 * Get quests the player has to complete before being able to start this quest
	 * @return array
	 */
	public function getDependencies ($level = 1);

	/**
	 * Verify if the conditions for completing this quest have been satisfied
	 * @param Entities\Quest
	 * @param DateTime
	 * @return bool
	 */
	public function isCompleted (Entities\Quest $quest, $term);

	/**
	 * Reward a clan for completing this quest
	 * @param Entities\Quest
	 * @return void
	 */
	public function processCompletion (Entities\Quest $quest);

	/**
	 * Get the score value of this quest
	 * @return int
	 */
	public function getValue ($level = 1);

	/**
	 * Get this quest's maximal level
	 * @return int
	 */
	public function getLevelCap ();
}
