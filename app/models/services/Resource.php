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
}