<?php
namespace Repositories;
use Entities;

class Facility extends BaseRepository
{
	/**
	 * Get the clan facilities
	 * @param Entities\Clan
	 * @return array of Entities\Facility
	 */
	public function getClanFacilities (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('max(f.level) level', 'f.type')
			->from($this->getEntityName(), 'f')->innerJoin('f.location', 'l')
			->where($qb->expr()->eq('l.owner', $clan->id))
			->groupBy('f.type');
		$result = array();
		foreach ($qb->getQuery()->getArrayResult() as $row) {
			$result[$row['type']] = $row['level'];
		}
		return $result;
	}

	/**
	 * Get the clan available facility changes
	 * @param Entities\Clan
	 * @return
	 */
	public function getAvailableFacilityChanges (Entities\Clan $clan)
	{
		$qb = $this->createQueryBuilder('f')->innerJoin('f.location', 'l');
		$qb->where($qb->expr()->eq('l.owner', $clan->id));
		$qb->groupBy('f.type, f.level');
		$result = array(
			'upgrades' => array(),
			'downgrades' => array(),
			'demolitions' => array()
		);
		$stats = $this->context->stats->construction;
		foreach ($qb->getQuery()->getResult() as $facility) {
			if ($facility->level < $this->context->params['game']['stats']['facilityLevelCap']) {
				if (!isset($result['upgrades'][$facility->type])) {
					$result['upgrades'][$facility->type] = array();
				}
				$level = $facility->level + 1;
				$info = array();
				$info['cost'] = $stats->getConstructionCost($clan, $facility->type, $level);
				$info['time'] = $stats->getConstructionTime($clan, $facility->type, $level);
				$result['upgrades'][$facility->type][$level] = $info;
			}
			if ($facility->level > 1) {
				if (!isset($result['downgrades'][$facility->type])) {
					$result['downgrades'][$facility->type] = array();
				}
				$level = $facility->level - 1;
				$info = array();
				$info['cost'] = $stats->getDemolitionCost($clan, $facility->type, $facility->level, $level);
				$info['time'] = $stats->getDemolitionTime($clan, $facility->type, $facility->level, $level);
				$result['downgrades'][$facility->type][$level] = $info;
			}
			if (!isset($result['demolitions'][$facility->type])) {
				$result['demolitions'][$facility->type] = array();
			}
			$info = array();
			$info['cost'] = $stats->getDemolitionCost($clan, $facility->type, $facility->level, 0);
			$info['time'] = $stats->getDemolitionTime($clan, $facility->type, $facility->level, 0);
			$result['demolitions'][$facility->type][$facility->level] = $info;
		}
		return $result;
	}
}