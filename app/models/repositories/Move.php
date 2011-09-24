<?php
namespace Repositories;
use Entities;
use Doctrine;

class Move extends BaseRepository 
{
	public function getColonisationCount (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select($qb->expr()->count('m.id'))
			->from($this->getEntityName(), 'm')
			->where($qb->expr()->andX(
				$qb->expr()->eq('m.owner', $clan->id),
				$qb->expr()->eq('m.type', '?1')
			));
		$qb->setParameter(1, 'colonisation');
		return $qb->getQuery()->getSingleScalarResult();
	}
}