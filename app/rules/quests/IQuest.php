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
	 * @param Entities\Quest
	 * @return string
	 */
	public function getExplanation (Entities\Quest $quest);

	/**
	 * Get quests the player has to complete before being able to start this quest
	 * @param int
	 * @return array
	 */
	public function getDependencies ($level = 1);

	/**
	 * Verify if the conditions for completing this quest have been satisfied
	 * @param Entities\Quest
	 * @param DateTime
	 * @return bool
	 */
	public function isCompleted (Entities\Quest $quest, $term = null);

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

	/**
	 * Get target
	 * @return int
	 */
	public function getTarget (Entities\Quest $quest);

	/**
	 * Get status
	 * @return int
	 */
	public function getStatus (Entities\Quest $quest);


}
