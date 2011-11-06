<?php
namespace Repositories;
use Entities;
use Doctrine;

/*
 * Shipment repository class
*/
class Shipment extends BaseRepository {
	/**
	 * Returns all clans running trades
	 * @param Entities\Clan
	 * @return array of Entities\Event
	 */
	public function getRunningShipments ($clan)
	{
		$qb = $this->createQueryBuilder('e');
		$qb->where($qb->expr()->andX(
			$qb->expr()->orX(
				$qb->expr()->eq('e.origin', $clan->headquarters->id),
				$qb->expr()->eq('e.target', $clan->headquarters->id)
			),
			$qb->expr()->eq('e.processed', '?1')
		))->orderBy('e.term');
		$qb->setParameter(1, FALSE);
		$offers = $qb->getQuery()->getResult();

		return $offers;
	}
}
