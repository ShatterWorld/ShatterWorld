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
	
	public function getColonisationCount (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select($qb->expr()->count('m.id'))
			->from($this->getEntityName(), 'm')
			->innerJoin('m.event', 'e')
			->where($qb->expr()->andX(
				$qb->expr()->eq('m.clan', $clan->id),
				$qb->expr()->eq('e.type', '?1')
			));
		$qb->setParameter(1, 'colonisation');
		return $qb->getQuery()->getSingleScalarResult();
	}
}