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
	 * Finds clans which offers are visible to given clan
	 * @param Entities\Clan
	 * @param integer
	 * @return ArraySet of Entities\Clan
	 */
	public function getDealers ($clan, $initDepth, $startup = true)
	{
		$fifo = $this->getVisibleClans($clan);
		$depths = new ArraySet();
		$dealers = new ArraySet();

		foreach($fifo as $dealer){
			$depths->addElement($dealer->id, $initDepth-1);
		}

		foreach($fifo as $dealer){
//Debugger::barDump($fifo);

			$id = $dealer->id;
			$dealers->addElement($id, $dealer);

			$dealersVisibleClans = $this->getVisibleClans($dealer);
//Debugger::barDump($dealersVisibleClans);
			foreach($dealersVisibleClans as $dvc){
				if($depths->offsetGet($id) < 1) continue;

				$dvcId = $dvc->id;
				if ($depths->offsetExists($dvcId)){
					if($depths->offsetGet($dvcId) < $depths->offsetGet($id)-1){
						$depths->updateElement($dvcId, $depths->offsetGet($id)-1);
						$fifo->updateElement($dvc->id, $dvc);
					}
				}
				else{
					$depths->addElement($dvcId, $depths->offsetGet($id)-1);
					$fifo->addElement($dvcId, $dvc);
				}

			}

			$fifo->deleteElement($dealer->id);
		}

		return $dealers;

	}

	/**
	 * Finds clans which are visible for the given clan
	 * @param Entities\Clan
	 * @return ArraySet of Entities\Clan
	 */
	public function getVisibleClans ($clan)
	{
		$visibleFields = $this->context->model->getFieldRepository()->getVisibleFields ($clan, $this->context->stats->getVisibilityRadius($clan));

		$visibleClans = new ArraySet();
		foreach ($visibleFields as $visibleField){
			if($visibleField->owner !== null){
				if($visibleClans->addElement($visibleField->owner->id, $visibleField->owner)){
				}
			}
		}
		return $visibleClans;
	}




}
