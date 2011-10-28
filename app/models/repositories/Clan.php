<?php
namespace Repositories;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;
use ArraySet;
use Graph;
use Nette;

class Clan extends BaseRepository
{
	/**
	 * Returns a clan specified by user given
	 * @param Entities\User
	 * @return Entities\Clan
	 */
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

	/**
	 * Returns a players clan
	 * @return Entities\Clan
	 */
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
	 * Returns oriented graph where key is clan index, and value distance
	 * The form $from->$target
	 * @param Entities\Clan
	 * @param integer
	 * @return array of array of int
	 */
	public function getDealersGraph ($clan, $initDepth)
	{
		$fifo = ArraySet::from($this->getVisibleClans($clan));
		$depths = new ArraySet();
		$dealers = new ArraySet();
		$graph = new Graph();

		foreach($fifo as $dealer){
			$depths->addElement($dealer->id, $initDepth-1);
		}

		foreach($fifo as $dealer){
			$id = $dealer->id;
			$dealers->addElement($id, $dealer);

			$dealersVisibleClans = $this->getVisibleClans($dealer);
			foreach($dealersVisibleClans as $dvc){
				if($depths->offsetGet($id) < 1) continue;

				$dvcId = $dvc->id;
				$graph->addEdge($id, $dvcId, $this->context->model->getFieldRepository()->calculateDistance($dealer->headquarters, $dvc->headquarters));

				if ($depths->offsetExists($dvcId)){
					if($depths->offsetGet($dvcId) < $depths->offsetGet($id)-1){
						$depths->updateElement($dvcId, $depths->offsetGet($id)-1);
						$fifo->updateElement($dvcId, $dvc);
					}
				}
				else{
					$depths->addElement($dvcId, $depths->offsetGet($id)-1);
					$fifo->addElement($dvcId, $dvc);
				}

			}

			$fifo->deleteElement($dealer->id);
		}
		return $graph;
	}

	/**
	 * Finds clans which are visible for the given clan
	 * @param Entities\Clan
	 * @return array of Entities\Clan
	 */
	public function getVisibleClans ($clan)
	{
		$qb = $this->createQueryBuilder('c');
		$qb->where($qb->expr()->in('c.id', $this->getVisibleClansIds($clan)));
		return $qb->getQuery()->getResult();
	}

	/**
	 * Finds ids of clans which are visible for the given clan
	 * @param Entities\Clan
	 * @return array of int
	 */
	protected function getVisibleClansIds ($clan)
	{
		$cache = $this->getVisibleClansCache();
		$ids = $cache->load($clan->id);
		if ($ids === NULL) {
			$ids = $this->computeVisibleClans($clan);
			$cache->save($clan->id, $ids);
		}
		return $ids;
	}

	/**
	 * Sets the visible clans cache
	 * @param Entities\Clan
	 * @return void
	 */
	protected function computeVisibleClans ($clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('o.id')->from('Entities\Field', 'f')->innerJoin('f.owner', 'o')
			->where($qb->expr()->in('f.id', $this->context->model->getFieldRepository()->getVisibleFieldsIds($clan, $this->context->stats->getVisibilityRadius($clan))))
			->groupBy('o.id');
		return array_map(function ($val) {return $val['id'];}, $qb->getQuery()->getArrayResult());
	}

	/**
	 * Returns cache of visible clans
	 * @return Cache
	 */
	public function getVisibleClansCache ()
	{
		return new Nette\Caching\Cache($this->context->cacheStorage, 'VisibleClans');
	}
}
