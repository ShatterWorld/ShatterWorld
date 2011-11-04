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

		$clanRepository = $this->context->model->getClanRepository();
		$graph = $clanRepository->getDealersGraph($clan, $depth);
		//Debugger::barDump($graph);
		$dealersIds = $graph->getVertices();

		$dealers = new ArraySet();
		foreach($dealersIds as $id){
			$dealers->addElement($id, $clanRepository->find($id));
		}

		$visibleOffers = array();
		foreach($offers as $offer){
			if($dealers->offsetExists($offer->owner->id)){
				$visibleOffers[] = $offer;
				$mediators = $this->getMediators($clan, $offer, $graph);//put somewhere else
				//Debugger::barDump($mediators);
			}
		}

		return $visibleOffers;
	}

	/**
	 * Returns the array of mediators
	 * @param Entities\Clan
	 * @param Entities\Offer
	 * @param utils\Graph
	 * @return array of Entities\Clan
	 */
	public function getMediators ($clan, $offer, $graph=null)
	{
		$clanRepository = $this->context->model->getClanRepository();
		if ($graph === null){
			$graph = $clanRepository->getDealersGraph($clan, $depth);
		}
		$path = $graph->getPath($clan->id, $offer->owner->id);

		$mediators = array();
		foreach($path as $mediator){
			$mediators[] = $clanRepository->find($mediator);
		}

		return $mediators;
	}
}










