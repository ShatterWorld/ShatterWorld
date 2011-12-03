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
		$production = $this->context->stats->resources->getProduction($clan);
		foreach ($this->getRepository()->findByClan($clan->id) as $account) {

			if (!isset($production[$account->type])){
				$production[$account->type] = 0;
			}

			$account->setProduction($production[$account->type], $term ?: new \DateTime());

			if ($production[$account->type] < 0){
				$rule = $this->context->rules->get('resource', $account->type);
				$rule->processExhaustion($clan, -$production[$account->type]);
			}

			$account->setProduction(isset($production[$account->type]) ? $production[$account->type] : 0, $term ?: new \DateTime());
		}
		$this->entityManager->flush();

	}

	public function recalculateStorage (Entities\Clan $clan, $term = NULL)
	{
		$storage = $this->context->stats->resources->getStorage($clan);
		foreach ($this->getRepository()->findByClan($clan->id) as $account) {
			$account->setStorage($storage, $term ?: new \DateTime());
		}
		$this->entityManager->flush();
	}
}
