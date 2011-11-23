<?php
namespace Repositories;
use Doctrine\ORM\NoResultException;

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
			->leftJoin('a.orders', 'o')
			->leftJoin('u.clans', 'c');
		$qb->where($qb->expr()->eq('u.id', '?1'));
		$qb->setParameter(1, $this->context->user->id);
		$query = $qb->getQuery();
		$query->useResultCache(TRUE);
		try {
			return $query->getSingleResult();
		} catch (NoResultException $e) {
			return NULL;
		}
	}
}