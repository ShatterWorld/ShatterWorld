<?php
namespace Repositories;
use Doctrine;

class Clan extends BaseRepository
{
	public function findOneByUser ($user)
	{
		$qb = $this->createQueryBuilder('c');
		$qb->where($qb->expr()->eq('c.user', $user));
		try {
			return $qb->getQuery()->useResultCache(true)->getSingleResult();
		} catch (Doctrine\ORM\NoResultException $e) {
			return NULL;
		}
	}
	
	public function getPlayerClan ()
	{
		return $this->findOneByUser($this->context->user->id);
	}
}