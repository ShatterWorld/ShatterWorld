<?php
namespace Repositories;
use Doctrine;
use Entities;

/*
 * Offer repository
 * @author Petr Bělohlávek
 * */
class Offer extends BaseRepository
{

	/*
	 * Returns all offers that can $clan accept
	 * @param Entities\Clan
	 * @param int
	 * @return void
	 * */
	public function findReachable ($clan, $depth)
	{
		$qb = $this->createQueryBuilder('o');
		$qb->where($qb->expr()->andX(
			$qb->expr()->neq('o.owner', $clan),
			$qb->expr()->eq('o.sold', false)
		));

		return $qb->getQuery()->getArrayResult();
	}

}
