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
		$qb = $this->createQueryBuilder('e');
		$qb->where($qb->expr()->andX(
			$qb->expr()->lte('e.term', '?1'),
			$qb->expr()->eq('e.processed', '?2')
		))->orderBy('e.term');
		$qb->setParameter(1, $now->format('Y-m-d H:i:s'));
		$qb->setParameter(2, FALSE);
		return $qb->getQuery()->getArrayResult();
	}
}