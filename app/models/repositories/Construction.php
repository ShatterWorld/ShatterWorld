<?php
namespace Repositories;
use Entities;
use Nette\Utils\Json;

class Construction extends BaseRepository
{
	public function getUsedUnitSlots (Entities\Clan $clan)
	{
		$qb = $this->createQueryBuilder('c');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('c.owner', '?0'),
			$qb->expr()->eq('c.type', '?1'),
			$qb->expr()->eq('c.processed', '?2'),
			$qb->expr()->gte('c.term', '?3')
		));
		$qb->setParameters(array(
			$clan->id,
			'unitTraining',
			FALSE,
			new \DateTime()
		));
		$result = array();
		foreach ($qb->getQuery()->getResult() as $training) {
			foreach (Json::decode($training->construction, Json::FORCE_ARRAY) as $type => $count) {
				foreach ($this->context->rules->get('unit', $type)->getDifficulty() as $slot => $amount) {
					if (array_key_exists($slot, $result)) {
						$result[$slot] = $result[$slot] + $amount * $count;
					} else {
						$result[$slot] = $amount * $count;
					}
				}
			}
		}
		return $result;
	}
}