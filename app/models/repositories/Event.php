<?php
namespace Repositories;
use Doctrine;

class Event extends BaseRepository {
	public function findPendingEvents ()
	{
		$now = new \DateTime;
		$qb = $this->createQueryBuilder('e');
		$qb->where($qb->expr()->lte('e.term', '?1'))
			->orderBy('e.term');
		$qb->setParameter(1, $now->format('Y-m-d H:i:s'));
		return $qb->getQuery()->getResult();
	}
}