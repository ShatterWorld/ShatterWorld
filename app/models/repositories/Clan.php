<?php
namespace Repositories;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;

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
	 * @param array of Entities\Clan
	 * @return array of Entities\Field
	 */
	public function getVisibleClans ($clan, $depth, &$visibleClans = array(), &$visitedClans = array(), &$functionStack = array())
	{
		/*
		 *	no recursion
		 * 	save function pointers to the stack (arraySet) by current depth
		 * 	run the methods from the stack (no recusions, just to save another pointers to set[depth-1])
		 * 	run...
		 * 	return
		 * */


		if ($depth <= 0) return;

		$visibleFields = $this->context->model->getFieldRepository()->getVisibleFields ($clan, $this->context->stats->getVisibilityRadius($clan));
		//Debugger::barDump($visibleFields);

		$newClans = array();
		foreach ($visibleFields as $visibleField){
			if(array_search($visibleField->owner, $visibleClans, true) === false){
				if($visibleField->owner !== null){
					$visibleClans[] = $visibleField->owner;
					$newClans[] = $visibleField->owner;
				}
			}
		}

		foreach ($newClans as $newClan){
			$this->getVisibleClans($newClan, $depth-1, $visibleClans);
		}

		return $visibleClans;

	}



}
