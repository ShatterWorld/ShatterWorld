<?php
namespace Repositories;
use Entities;
use Doctrine;

class Move extends BaseRepository 
{
	public function getEventInfo ($eventId)
	{
		$qb = $this->getEntityManager()->createQueryBuilder()
			->select('m', 'o', 't')
			->from($this->getEntityName(), 'm')
			->leftJoin('m.origin', 'o')
			->leftJoin('m.target', 't');
		$qb->where($qb->expr()->eq('m.event', $eventId));
		return $qb->getQuery()->getSingleResult(Doctrine\ORM\Query::HYDRATE_ARRAY);
	}
}