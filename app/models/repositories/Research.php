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

		$indexedRes = array();

		foreach($res as $r){
			$indexedRes[$r->type] = $r;
		}

		return $indexedRes;
	}

	public function getClanResearch ($clan, $type)
	{
		$qb = $this->createQueryBuilder('r');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('r.owner', '?0'),
			$qb->expr()->eq('r.type', '?1')
		));
		$qb->setParameters(array(
			$clan->id,
			$type
		));
		$res = $qb->getQuery()->getResult();
		foreach($res as $r){
			return $r;
		}
		return null;
	}
}
