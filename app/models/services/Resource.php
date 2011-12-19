<?php
namespace Services;
use Entities;
use Rules;
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
	public function pay (Entities\Clan $clan, $price, $term = NULL, $flush = TRUE)
	{
		if (!$this->getRepository()->checkResources($clan, $price, $term)) {
			throw new \InsufficientResourcesException;
		}
		$resources = $this->getRepository()->findResourcesByClan($clan);
		foreach ($price as $resource => $cost) {
			$resources[$resource]->pay($cost);
		}
		$this->checkExhaustion($clan, $term);
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
	public function increase (Entities\Clan $clan, $payment, $term = NULL, $flush = TRUE)
	{
		$resources = $this->getRepository()->findResourcesByClan($clan);
		foreach ($payment as $resource => $amount) {
			$resources[$resource]->increase($amount);
		}
		$this->checkExhaustion($clan, $term);
		if ($flush) {
			$this->entityManager->flush();
		}
	}

	public function recalculateProduction (Entities\Clan $clan, $term = NULL)
	{
		$production = $this->context->stats->resources->getProduction($clan);
		$this->entityManager->flush();
		foreach ($this->getRepository()->findByClan($clan->id) as $account) {
			if (!isset($production[$account->type])){
				$production[$account->type] = 0;
			}
			$account->setProduction($production[$account->type], $term ?: new \DateTime());
		}
		$this->entityManager->flush();
		$this->checkExhaustion($clan, $term);

	}

	public function recalculateStorage (Entities\Clan $clan, $term = NULL)
	{
		$storage = $this->context->stats->resources->getStorage($clan);
		foreach ($this->getRepository()->findByClan($clan->id) as $account) {
			$account->setStorage($storage, $term ?: new \DateTime());
		}
		$this->entityManager->flush();
	}

	protected function checkExhaustion (Entities\Clan $clan, $term = NULL)
	{
		$production = $this->context->stats->resources->getProduction($clan);
		foreach ($this->getRepository()->findByClan($clan->id) as $account) {
			if ($this->context->rules->get('resource', $account->type) instanceof Rules\Resources\IExhaustableResource) {
				if ($production[$account->type] < 0){
					$production = $this->startExhaustionCountdown($clan, $account, $term);
				} else {
					if ($countdown = $this->getExhaustionCountdown($clan, $account->type)) {
						$this->context->model->constructionService->delete($countdown);
					}
				}
			}
		}
	}

	protected function getExhaustionCountdown (Entities\Clan $clan, $resource)
	{
		return $this->context->model->constructionRepository->findOneBy(array(
			'owner' => $clan->id,
			'type' => 'resourceExhaustion',
			'construction' => $resource,
			'processed' => FALSE
		));
	}

	protected function startExhaustionCountdown (Entities\Clan $clan, Entities\Resource $resource, $term = NULL, $flush = TRUE)
	{
		$resource->settleBalance($term);
		$production = $this->context->stats->resources->getProduction($clan);

		$time = floor($resource->balance / (-$production[$resource->type]));
		$term = $term ?: new \DateTime();
		if (!($countdown = $this->getExhaustionCountdown($clan, $resource->type))) {
			$countdown = $this->context->model->constructionService->create(array(
				'owner' => $clan,
				'target' => $clan->getHeadquarters(),
				'type' => 'resourceExhaustion',
				'construction' => $resource->type
			), FALSE);
		}
		$countdown->setTimeout($time, $term);
		if ($flush) {
			$this->entityManager->flush();
		}
	}
}
