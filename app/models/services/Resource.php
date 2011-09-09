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
	public function pay (Entities\Clan $clan, $price)
	{
		$resources = $this->getRepository()->findResourcesByClan($clan);
		foreach ($price as $resource => $cost) {
			if (!$resources[$resource]->has($cost)) {
				throw new \InsufficientResourcesException;
			}
		}
		foreach ($price as $resource => $cost) {
			$resources[$resource]->pay($cost);
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
			$this->entityManager->persist($resources[$resource]);
		}
		$this->entityManager->flush();
	}
	
	public function recalculateProduction (Entities\Clan $clan)
	{
		foreach ($this->context->rules->getAll('resource') as $resource => $rule) {
			$this->update($this->getRepository()->findOneBy(array('clan' => $clan->id, 'type' => lcfirst($resource))), 
				array('production' => $this->context->stats->getResourceProduction($clan, lcfirst($resource))));
		}
	}
}