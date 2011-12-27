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

	public function getRemoteExploration (Entities\Clan $clan, $distance)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('e');
		$qb->from($this->getEntityName(), 'e');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('e.owner', $clan->id),
			$qb->expr()->eq('e.processed', '?1'),
			$qb->expr()->eq('e.type', '?2')
		));

		$qb->setParameter(1, TRUE);
		$qb->setParameter(2, 'exploration');
		$explorations = $qb->getQuery()->getResult();

		$count = 0;
		$hq = $clan->headquarters;
		$hqArr = array('x' => $hq->getX(), 'y' => $hq->getY());
		foreach($explorations as $exploration){
			$target = $exploration->target;
			$targetArr = array('x' => $target->getX(), 'y' => $target->getY());
			if ($this->context->map->calculateDistance($hqArr, $targetArr) >= $distance){
				$count++;
			}
		}
		return $count;
	}
}












