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
	 * @return array of Entities\Offer
	 */
	public function findReachable ($clan)
	{
		$stats = $this->context->stats;
		$depth = $stats->getTradingRadius($clan);

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('o', 'c', 'h')->from($this->getEntityName(), 'o')
			->innerJoin('o.owner', 'c')
			->innerJoin('c.headquarters', 'h');
		$qb->where($qb->expr()->andX(
			$qb->expr()->neq('o.owner', $clan->id),
			$qb->expr()->eq('o.sold', '?1'),
			$qb->expr()->in('o.owner', $this->context->model->getClanRepository()->getDealersGraph($clan, $depth)->getVerticesIds())
		));
		$qb->setParameter(1, false);
		$offers = $qb->getQuery()->getResult();

		$fieldRepository = $this->context->model->getFieldRepository();
		$hq = $clan->headquarters;

		@usort($offers, function($a, $b) use ($fieldRepository, $hq, $stats){
			$timeA = $fieldRepository->calculateDistance($a->owner->headquarters, $hq) * $stats->getMerchantSpeed($a->owner);
			$timeB = $fieldRepository->calculateDistance($b->owner->headquarters, $hq) * $stats->getMerchantSpeed($b->owner);

			if ($timeA == $timeB) {
				return 0;
			}
			return ($timeA < $timeB) ? -1 : 1;
		});

		return $offers;
	}

	/**
	 * Returns the array of mediators
	 * @param int
	 * @param Entities\Clan
	 * @param Entities\Offer
	 * @param utils\Graph
	 * @return array of Entities\Clan
	 */
	public function getMediators ($pathType, $clan, $offer, $graph=null)
	{
		$clanRepository = $this->context->model->getClanRepository();
		if ($graph === null){
			$graph = $clanRepository->getDealersGraph($clan, 7); //get const !!!
		}
		$path = $graph->getPath($pathType, $clan->id, $offer->owner->id);

		$mediators = array();
		foreach($path as $mediator){
			$mediators[] = $clanRepository->find($mediator);
		}

		return $mediators;
	}

	/**
	 * Returns the array of mediators profits
	 * @param int
	 * @param Entities\Clan
	 * @param Entities\Offer
	 * @param utils\Graph
	 * @return array of int
	 */
	public function getMediatorProfits ($pathType, $clan, $offer, $graph=null)
	{
		$mediators = $this->getMediators($pathType, $clan, $offer, $graph);

		$profits = array();
		$resLeft = $offer->offerAmount;
		$stats = $this->context->stats;
		foreach($mediators as $mediator){
			$profit = floor($resLeft * max($stats->getTradeProfit($mediator) - $stats->getTradeProfit($clan), 0));
			$resLeft -= $profit;
			$profits[$mediator->id] = $profit;
		}
		//Debugger::barDump($profits);
		return $profits;
	}

	/**
	 * Returns the total mediators profit
	 * @param int
	 * @param Entities\Clan
	 * @param Entities\Offer
	 * @param utils\Graph
	 * @return int
	 */
	public function getTotalMediatorProfit ($pathType, $clan, $offer, $graph=null)
	{
		$profits = $this->getMediatorProfits($pathType, $clan, $offer, $graph);
		$totalProfit = 0;
		foreach($profits as $profit){
			$totalProfit += $profit;
		}

		return $totalProfit;
	}
}










