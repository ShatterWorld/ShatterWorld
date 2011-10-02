<?php
namespace Services;
use Entities;

class Resource extends BaseService
{
	/**
	 * Bill the clan given price
	 * @param Entities\Clan
	 * @param array of $resource => $cost
	 * @throws InsufficientResourcesException
	 * @return void
	 */
	public function pay (Entities\Clan $clan, $price, $flush = TRUE)
	{
		if (!$this->getRepository()->checkResources($clan, $price)) {
			throw new \InsufficientResourcesException;
		}
		$resources = $this->getRepository()->findResourcesByClan($clan);
		foreach ($price as $resource => $cost) {
			$resources[$resource]->pay($cost);
		}
		if ($flush) {
			$this->entityManager->flush();
		}
	}
	
	/**
	 * Transfer resources to given clan
	 * @param Entities\Clan
	 * @param array of $resource => $amount
	 * @return void
	 */
	public function increase (Entities\Clan $clan, $payment)
	{
		$resources = $this->getRepository()->findResourcesByClan($clan);
		foreach ($payment as $resource => $amount) {
			$resources[$resource]->increase($amount);
		}
		$this->entityManager->flush();
	}
	
	public function recalculateProduction (Entities\Clan $clan, $term)
	{
		$production = $this->context->stats->getResourcesProduction($clan);
		foreach ($this->getRepository()->findByClan($clan->id) as $account) {
			$account->setProduction(isset($production[$account->type]) ? $production[$account->type] : 0, $term);
		}
		$this->entityManager->flush();
	}
}