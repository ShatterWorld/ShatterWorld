<?php
namespace Repositories;
use Entities;

class Report extends BaseRepository
{
	public function findUnread (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('r', 'e')->from($this->getEntityName(), 'r')->innerJoin('r.event', 'e');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('e.owner', '?1'),
			$qb->expr()->eq('r.isRead', '?2')
		));
		$qb->setParameter(1, $clan->id);
		$qb->setParameter(2, FALSE);
		return $qb->getQuery()->getResult();
	}
}