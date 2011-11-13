<?php
namespace Repositories;
use Entities;
use Doctrine;

/**
 * Research repository class
 * @author Petr Bělohlávek
 */
class Research extends BaseRepository
{
	/**
	 * Returns researched
	 * @param Entities\Clan
	 * @return array of Entities\Research
	 */
	public function getResearched ($clan)
	{
		$qb = $this->createQueryBuilder('r');
		$qb->where($qb->expr()->eq('r.owner', $clan->id));
		$res = $qb->getQuery()->getResult();
		return $res;
	}

}
