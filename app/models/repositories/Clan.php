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
	 * @return array of Entities\Field
	 */
	public function getVisibleClans ($clan, $depth)
	{
		$visibleFields = $this->context->model->getFieldRepository()->getVisibleFields ($clan, $depth);

		Debugger::barDump($visibleFields);
		$visibleClans = array();

		foreach($visibleFields as $visibleField){
			if(array_search($visibleField->owner, $visibleClans, true) === false){
				$visibleClans[] = $visibleField->owner;
			}
		}







	}



}
