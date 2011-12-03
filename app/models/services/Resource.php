<?php
namespace Services;
use Entities;
use Nette\Diagnostics\Debugger;

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
	public function increase (Entities\Clan $clan, $payment, $flush = TRUE)
	{
		$resources = $this->getRepository()->findResourcesByClan($clan);
		foreach ($payment as $resource => $amount) {
			$resources[$resource]->increase($amount);
		}
		if ($flush) {
			$this->entityManager->flush();
		}
	}

	public function recalculateProduction (Entities\Clan $clan, $term = NULL)
	{
		$production = $this->context->stats->getResourcesProduction($clan);
		foreach ($this->getRepository()->findByClan($clan->id) as $account) {

			if (isset($production[$account->type])){
				$newProduction = $production[$account->type] * $this->context->stats->getProductionCoefficient($clan, $account->type);
			}
			else{
				$newProduction = 0;
			}

			$account->setProduction($newProduction, $term ?: new \DateTime());

			if ($newProduction < 0){
				$rule = $this->context->rules->get('resource', $account->type);
				$rule->processExhaustion($clan, -$newProduction);
			}

		}
		$this->entityManager->flush();

	}

	public function recalculateStorage (Entities\Clan $clan, $term = NULL)
	{
		$storage = $this->context->stats->getResourceStorage($clan);
		foreach ($this->getRepository()->findByClan($clan->id) as $account) {
			$account->setStorage($storage, $term ?: new \DateTime());
		}
		$this->entityManager->flush();
	}
}
