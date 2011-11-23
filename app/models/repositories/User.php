<?php
namespace Repositories;

class User extends BaseRepository
{
	/**
	 * Get current user
	 * @return Entities\User
	 */
	public function getActiveUser ()
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('u', 'a', 'c', 'o')
			->from($this->getEntityName(), 'u')
			->leftJoin('u.activeClan', 'a')
			->innerJoin('a.orders', 'o')
			->leftJoin('u.clans', 'c');
		$qb->where($qb->expr()->eq('u.id', '?1'));
		$qb->setParameter(1, $this->context->user->id);
		$query = $qb->getQuery();
		$query->useResultCache(TRUE);
		return $query->getSingleResult();
	}
}