<?php
namespace Stats;
use Entities;
use Nette\Diagnostics\Debugger;
use Nette\Caching\Cache;

class Score extends AbstractStat
{

	/**
	 * Cache of score
	 * @var Nette\Caching\Cache
	 */
	protected $scoreCache;

	/**
	 * Returns the cache
	 * @return Nette\Caching\Cache
	 */
	public function getScoreCache ()
	{
		if ($this->scoreCache === null){
			$this->scoreCache = new Cache($this->getContext()->cacheStorage, 'Score');
		}
		return $this->scoreCache;
	}

	/**
	 * Get the score of the clan
	 * @param Entities\Clan
	 * @return array of int
	 */
	public function getClanScore (Entities\Clan $clan)
	{
		$score = $this->getScoreCache()->load($clan->id);
		if ($score !== null){
			return $score;
		}

		$this->getClanSizeScore($clan);
		$this->getClanResearchScore($clan);
		$this->getClanUnitScore($clan);
		$this->getClanQuestScore($clan);
		$this->getClanFacilityScore($clan);
		$this->getClanResourceScore($clan);

		return $this->getScoreCache()->load($clan->id);
	}

	/**
	 * Get the total score of the clan
	 * @param Entities\Clan
	 * @return int
	 */
	public function getTotalClanScore (Entities\Clan $clan)
	{
		$score = 0;
		foreach($this->getClanScore($clan) as $value){
			$score += $value;
		}
		return $score;
	}

	/**
	 * Get the territory size score of clan
	 * @param Entities\Clan
	 * @return float
	 */
	public function getClanSizeScore (Entities\Clan $clan)
	{
		$cachedScore = $this->getScoreCache()->load($clan->id);
		if ($cachedScore === null){
			$scoreArray = $this->getClanScore($clan);
			return $scoreArray['size'];
		}

		$score = 100 * $this->getContext()->model->getFieldRepository()->getTerritorySize($clan);
		$cachedScore['size'] = $score;

		$this->getScoreCache()->save($clan->id, $cachedScore);
		return floor($score);
	}

	/**
	 * Get the research score of clan
	 * @param Entities\Clan
	 * @return float
	 */
	public function getClanResearchScore (Entities\Clan $clan)
	{
		$cachedScore = $this->getScoreCache()->load($clan->id);
		if ($cachedScore === null){
			$scoreArray = $this->getClanScore($clan);
			return $scoreArray['research'];
		}

		$score = 0;
		foreach($this->getContext()->model->getResearchRepository()->getResearched($clan) as $research){
			$score += $this->getContext()->rules->get('research', $research->type)->getValue($research->level);
		}
		$cachedScore['researches'] = $score;

		$this->getScoreCache()->save($clan->id, $cachedScore);
		return floor($score);
	}

	/**
	 * Get the unit score of clan
	 * @param Entities\Clan
	 * @return float
	 */
	public function getClanUnitScore (Entities\Clan $clan)
	{
		$cachedScore = $this->getScoreCache()->load($clan->id);
		if ($cachedScore === null){
			$scoreArray = $this->getClanScore($clan);
			return $scoreArray['unit'];
		}

		$score = 0;
		foreach($this->getContext()->model->getUnitRepository()->getClanUnits($clan) as $unit){
			$score += $this->getContext()->rules->get('unit', $unit->type)->getValue() * $unit->count;
		}
		$cachedScore['unit'] = $score;

		$this->getScoreCache()->save($clan->id, $cachedScore);
		return floor($score);
	}

	/**
	 * Get the quest score of clan
	 * @param Entities\Clan
	 * @return float
	 */
	public function getClanQuestScore (Entities\Clan $clan)
	{
		$cachedScore = $this->getScoreCache()->load($clan->id);
		if ($cachedScore === null){
			$scoreArray = $this->getClanScore($clan);
			return $scoreArray['quest'];
		}

		$score = 0;
		foreach($this->getContext()->model->getQuestRepository()->findCompleted($clan) as $quest){
			$score += $this->getContext()->rules->get('quest', $quest->type)->getValue($quest->level);
		}
		$cachedScore['quest'] = $score;

		$this->getScoreCache()->save($clan->id, $cachedScore);
		return floor($score);
	}

	/**
	 * Get the facilities score of clan
	 * @param Entities\Clan
	 * @return float
	 */
	public function getClanFacilityScore (Entities\Clan $clan)
	{
		$cachedScore = $this->getScoreCache()->load($clan->id);
		if ($cachedScore === null){
			$scoreArray = $this->getClanScore($clan);
			return $scoreArray['facility'];
		}

		$score = 0;
		foreach($this->getContext()->model->getFieldRepository()->getClanFacilities($clan) as $type => $level){
			$score += $this->getContext()->rules->get('facility', $type)->getValue($level);
		}
		$cachedScore['facility'] = $score;

		$this->getScoreCache()->save($clan->id, $cachedScore);
		return floor($score);
	}

	/**
	 * Get the resources score of clan
	 * @param Entities\Clan
	 * @return float
	 */
	public function getClanResourceScore (Entities\Clan $clan)
	{
		$cachedScore = $this->getScoreCache()->load($clan->id);
		if ($cachedScore === null){
			$scoreArray = $this->getClanScore($clan);
			return $scoreArray['resource'];
		}

		$score = 0;
		foreach($this->getContext()->model->getResourceRepository()->findResourcesByClan($clan) as $resource){
			$score += $this->getContext()->rules->get('resource', $resource->type)->getValue($resource);
		}
		$cachedScore['resource'] = $score;

		$this->getScoreCache()->save($clan->id, $cachedScore);
		return floor($score);
	}

}
