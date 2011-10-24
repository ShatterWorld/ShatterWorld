<?php
namespace Repositories;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;

/**
 * Offer repository
 * @author Petr Bělohlávek
 */
class Offer extends BaseRepository
{

	/**
	 * Returns all offers that can $clan accept
	 * @param Entities\Clan
	 * @param int
	 * @return void
	 */
	public function findReachable ($clan, $depth=1)
	{
		$qb = $this->createQueryBuilder('o');
		$qb->where($qb->expr()->andX(
			$qb->expr()->neq('o.owner', '?1'),
			$qb->expr()->eq('o.sold', '?2')
		));
		$qb->setParameter(1, $clan->id);
		$qb->setParameter(2, false);

		$offers = $qb->getQuery()->getResult();
		//Debugger::barDump($offers);

		$visibleClans = $this->context->model->getClanRepository()->getVisibleClans($clan, $depth);
		//Debugger::barDump($visibleClans);

		$visibleOffers = array();
		foreach($offers as $offer){
			if($visibleClans->offsetExists($offer->owner->id)){
				$visibleOffers[] = $offer;
			}
		}
		//Debugger::barDump($visibleOffers);

		return $visibleOffers;
	}

}
