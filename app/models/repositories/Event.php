<?php
namespace Repositories;
use Doctrine;

class Event extends BaseRepository {
	
	/**
	 * Finds pending events locked by given user on given time
	 * @param int
	 * @param DateTime
	 * @return array
	 */
	public function findPendingEvents ($lockUserId, $lockTimeout)
	{
		$now = new \DateTime;
		$qb = $this->createQueryBuilder('e');
		$qb->where($qb->expr()->andX(
				$qb->expr()->lte('e.term', '?1'),
				$qb->expr()->eq('e.lockUserId', '?3'),
				$qb->expr()->eq('e.lockTimeout', '?4'),
				$qb->expr()->eq('e.processed', '?2')))
			->orderBy('e.term');
		$qb->setParameter(1, $now->format('Y-m-d H:i:s'));
		$qb->setParameter(2, FALSE);
		$qb->setParameter(3, $lockUserId);
		$qb->setParameter(4, $lockTimeout);
		return $qb->getQuery()->getResult();
	}
}