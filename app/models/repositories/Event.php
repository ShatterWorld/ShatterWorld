<?php
namespace Repositories;
use Entities;
use Doctrine;

class Event extends BaseRepository {
	
	/**
	 * Finds pending events locked by given user on given time
	 * @param int
	 * @param float
	 * @return array
	 */
	public function findPendingEvents ($lockUserId, $lockTimeout)
	{
		$now = new \DateTime;
		$qb = $this->createQueryBuilder('e');
		$qb->where($qb->expr()->andX(
				$qb->expr()->eq('e.lockUserId', '?1'),
				$qb->expr()->eq('e.lockTimeout', '?2')))
			->orderBy('e.term');
		$qb->setParameter(1, $lockUserId);
		$qb->setParameter(2, $lockTimeout);
		return $qb->getQuery()->getResult();
	}
	
	public function getUpcomingEventsArray (Entities\Clan $clan)
	{
		$now = new \DateTime;
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('e');
		$qb->from($this->getEntityName(), 'e');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('e.owner', '?1'),
			$qb->expr()->eq('e.processed', '?2')
		))->orderBy('e.term');
		$qb->setParameter(1, $clan->id);
		$qb->setParameter(2, FALSE);
		$query = $qb->getQuery();
		$result = array();
		foreach ($query->getResult() as $key => $event) {
			$result[$key] = $event->toArray();
			$result[$key]['countdown'] = max($event->term->format('U') - date('U'), 0);
			if ($event->target) {
				$result[$key]['target'] = $event->target->toArray();
			}
		}
		return $result;
	}
}