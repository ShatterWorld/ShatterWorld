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
	
	/**
	 * Finds upcoming events that are relevant to given clan
	 * @param Entities\Clan 
	 * @return array of Entities\Event
	 */
	public function findUpcomingEvents (Entities\Clan $clan)
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
		return $qb->getQuery()->getResult();
	}
}












