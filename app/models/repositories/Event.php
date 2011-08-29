<?php
namespace Repositories;
use Doctrine;

class Event extends BaseRepository {
	public function findPendingEvents ()
	{
		$now = new \DateTime;
		$qb = $this->createQueryBuilder('e');
		$qb->where($qb->expr()->andX($qb->expr()->lte('e.term', '?1'), $qb->expr()->eq('e.processed', '?2')))
			->orderBy('e.term');
		$qb->setParameter(1, $now->format('Y-m-d H:i:s'));
		$qb->setParameter(2, FALSE);
		return $qb->getQuery()->getResult();
	}
}