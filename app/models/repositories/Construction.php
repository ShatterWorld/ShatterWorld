<?php
namespace Repositories;
use Entities;
use Doctrine;

class Construction extends BaseRepository 
{
	public function getEventInfo ($eventId)
	{
		$qb = $this->getEntityManager()->createQueryBuilder()
			->select('c', 'f')
			->from($this->getEntityName(), 'c')
			->leftJoin('c.field', 'f');
		$qb->where($qb->expr()->eq('c.event', $eventId));
		return $qb->getQuery()->getSingleResult(Doctrine\ORM\Query::HYDRATE_ARRAY);
	}
}