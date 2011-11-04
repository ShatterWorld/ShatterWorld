<?php
namespace Repositories;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;
use ArraySet;
use Graph;
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

		$clanRepository = $this->context->model->getClanRepository();

		$graph = $clanRepository->getDealersGraph($clan, $depth);
		//Debugger::barDump($graph);
		$dealersIds = array_keys($graph->getGraph());

		$dealers = new ArraySet();
		foreach($dealersIds as $id){
			$dealers->addElement($id, $clanRepository->find($id));
		}
		//Debugger::barDump($dealers);

		$visibleOffers = array();
		foreach($offers as $offer){
			if($dealers->offsetExists($offer->owner->id)){
				$visibleOffers[] = $offer;
				$path = $graph->getPath($clan->id, $offer->owner->id);
				Debugger::barDump($path);
			}
		}

		//Debugger::barDump($visibleOffers);

		return $visibleOffers;
	}

}
