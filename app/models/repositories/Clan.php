<?php
namespace Repositories;

class Clan extends BaseRepository
{
	public function findOneByUser ($user)
	{
		$qb = $this->createQueryBuilder('c');
		$qb->where($qb->expr()->eq('c.user', $user));
		return $qb->getQuery()->useResultCache(true)->getSingleResult();
	}
}