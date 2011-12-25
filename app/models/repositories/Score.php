<?php
namespace Repositories;
use Entities;
use Doctrine;
use Nette\Diagnostics\Debugger;

/**
 * Score repository class
 * @author Petr Bělohlávek
 */
class Score extends BaseRepository
{
	/**
	 *	Get total clan score
	 * 	@param Entities\Clan
	 * 	@return int
	 */
	public function getTotalClanScore (Entities\Clan $clan)
	{
		$totalScore = 0;
		foreach($this->getClanScoreArray($clan) as $value){
			$totalScore += $value;
		}

		return $totalScore;
	}

	/**
	 *	Get clan score
	 * 	@param Entities\Clan
	 *	@param string
	 * 	@return int
	 */
	public function getClanScore (Entities\Clan $clan, $type)
	{
		$score = $this->findOneBy(array(
			'owner' => $clan->id,
			'type' => $type
		));

		return $score->value;
	}

	/**
	 *	Get clan score array
	 * 	@param Entities\Clan
	 * 	@return array of int
	 */
	public function getClanScoreArray (Entities\Clan $clan)
	{
		$scores = $this->findByOwner($clan->id);

		$scoreArray = array();
		foreach($scores as $score){
			$scoreArray[$score->type] = $score->value;
		}

		return $scoreArray;
	}

	/**
	 *	Get total alliance score
	 * 	@param Entities\Alliance
	 * 	@return int
	 */
	public function getTotalAllianceScore (Entities\Alliance $alliance)
	{
		$totalScore = 0;
		foreach($this->getAllianceScoreArray($alliance) as $value){
			$totalScore += $value;
		}
		return $totalScore;
	}


	/**
	 *	Get alliance score array
	 * 	@param Entities\Alliance
	 * 	@return array of array of int
	 */
	public function getAllianceScoreArray (Entities\Alliance $alliance)
	{
		$allianceScore = array();
		foreach ($alliance->members as $member){
			$allianceScore[$member->id] = $this->getTotalClanScore($member);
		}

		return $allianceScore;
	}

	/**
	 *	Get clan score array
	 * 	@return array
	 */
	public function getClanList ()
	{

	}

	/**
	 *	Get alliance score array
	 * 	@return array
	 */
	public function getAllianceList ()
	{

	}


}
