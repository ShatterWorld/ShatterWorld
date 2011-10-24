<?php
namespace Repositories;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;
use ArraySet;

class Clan extends BaseRepository
{
	public function findOneByUser ($user)
	{
		$qb = $this->createQueryBuilder('c');
		$qb->where($qb->expr()->eq('c.user', $user));
		try {
			return $qb->getQuery()->useResultCache(true)->getSingleResult();
		} catch (Doctrine\ORM\NoResultException $e) {
			return NULL;
		}
	}

	public function getPlayerClan ()
	{
		return $this->findOneByUser($this->context->user->id);
	}

	public function getAvailableOrders (Entities\Clan $clan)
	{
		$now = new \DateTime();
		$cap = $this->context->params['game']['stats']['orderCap'];
		$diff = $now->format('U') - $this->context->params['game']['start']->format('U');
		$orders = ($diff / $this->context->params['game']['stats']['orderTime']) - $clan->issuedOrders - $clan->expiredOrders;
		if ($orders > $cap) {
			$this->context->model->getClanService()->update($clan, array(
				'expiredOrders' => $clan->expiredOrders + ($orders - $cap)
			));
			return $cap;
		}
		return $orders;
	}


	/**
	 * Finds clans which are visible for the player
	 * @param Entities\Clan
	 * @param integer
	 * @param ArraySet of Entities\Clan
	 * @param ArraySet of Entities\Clan
	 * @param array of array of function
	 * @return ArraySet of Entities\Clan
	 */
	public function getVisibleClans ($clan, $depth = 1, &$visibleClans = null, &$visitedClans = null, &$functionStack = array(array()))
	{

		//$this->markClans();


		if ($depth <= 0) return $visibleClans;

		if ($visibleClans === null) $visibleClans = new ArraySet();
		if ($visitedClans === null) $visitedClans = new ArraySet();


		$visibleFields = $this->context->model->getFieldRepository()->getVisibleFields ($clan, $this->context->stats->getVisibilityRadius($clan));
		//Debugger::barDump($visibleFields);

		$newClans = new ArraySet();
		foreach ($visibleFields as $visibleField){
			if($visibleField->owner !== null){
				if($visibleClans->offsetSet($visibleField->owner->id, $visibleField->owner)){
					$newClans->offsetSet($visibleField->owner->id, $visibleField->owner);
				}
			}
		}

		foreach ($newClans as $newClan){
			$this->getVisibleClans($newClan, $depth-1, $visibleClans);
		}

		return $visibleClans;

	}

	protected function markClans($clan, $depth)
	{


	}




}
