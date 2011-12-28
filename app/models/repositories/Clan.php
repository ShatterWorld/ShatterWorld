<?php
namespace Repositories;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;
use Nette\Caching\Cache;
use ArraySet;
use Graph;
use Nette;

class Clan extends BaseRepository
{
	/**
	 * Cache of clan graphs
	 * @var Nette\Caching\Cache
	 */
	protected $clanGraphCache;

	/**
	 * Returns the cache
	 * @return Nette\Caching\Cache
	 */
	public function getClanGraphCache ()
	{
		if ($this->clanGraphCache === null){
			$this->clanGraphCache = new Cache($this->context->cacheStorage, 'ClanGraph');
		}
		return $this->clanGraphCache;
	}

	/**
	 * Returns a players clan
	 * @return Entities\Clan
	 */
	public function getPlayerClan ()
	{
		$user = $this->context->model->getUserRepository()->getActiveUser();
		if (!$user) {
			return NULL;
		} else {
			$activeClan = $user->getActiveClan();
			if (!$activeClan && ($clans = $user->getClans())) {
				$activeClan = array_pop($clans);
				$this->context->model->userService->update($user, array('activeClan' => $activeClan));
			}
			return $activeClan;
		}
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

		$cachedGraph = $this->getClanGraphCache()->load($clan->id);
		if ($cachedGraph !== null){
			return $cachedGraph;
		}

		$fifo = ArraySet::from($this->getVisibleClans($clan));
		$depths = new ArraySet();
		$dealers = new ArraySet();
		$graph = new Graph();

		foreach($fifo as $dealer){
			$depths->addElement($dealer->id, $initDepth-1);
		}

		foreach($fifo as $dealer){
			$id = $dealer->id;
			$graph->addVertice($id, 1);
			$dealers->addElement($id, $dealer);

			$dealersVisibleClans = $this->getVisibleClans($dealer);
			foreach($dealersVisibleClans as $dvc){
				if($depths->offsetGet($id) < 1) continue;

				$dvcId = $dvc->id;
				if ($dvcId != $id){
					$graph->addVertice($dvcId, $this->context->stats->trading->getProfit($dvc));
					$map = $this->context->map;
					$graph->addEdge($id, $dvcId, $map->calculateDistance($map->coords($dealer->headquarters), $map->coords($dvc->headquarters)));
				}
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
		$this->getClanGraphCache()->save($clan->id, $graph, array(
			Cache::TAGS => array_map(function ($id) {return "observer/$id";}, $graph->getVerticesIds())
		));

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
			->where($qb->expr()->andX(
				$qb->expr()->in('f.id', $this->context->model->getFieldRepository()->getVisibleFieldsIds($clan)),
				$qb->expr()->eq('o.deleted', '?1')
			))
			->groupBy('o.id');
		$qb->setParameter(1, FALSE);
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

	/**
	 * Counts the sum of distances fields
	 * @param Entities\Clan
	 * @param Entities\Clan
	 * @param array of Entities\Clan
	 * @return integer
	 */
	public function calculateTotalDistance ($origin, $target, $mediators)
	{
		$map = $this->context->map;
		if (count($mediators) <= 0) {
			return $map->calculateDistance($map->coords($origin), $map->coords($target));
		}
		$distance = 0;
		$first = true;
		$prev = null;

		foreach ($mediators as $mediator) {
			if ($first) {
				$first = false;
				$distance += $map->calculateDistance($map->coords($origin), $map->coords($mediator));
				$prev = $mediator;
				continue;
			}
			$distance += $map->calculateDistance($map->coords($prev), $map->coords($mediator));
			$prev = $mediator;
		}
		$distance += $map->calculateDistance($map->coords($prev), $map->coords($target));
		return $distance;
	}
}