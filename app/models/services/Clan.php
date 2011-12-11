<?php
namespace Services;
use Nette\Caching\Cache;
use Nette\Diagnostics\Debugger;
use Entities;
use ArraySet;

/**
 * Clan service class
 * @author Petr Bělohlávek
 */
class Clan extends BaseService
{
	/** @var Nette\Caching\Cache*/
	protected $cache;

	public function __construct ($context, $entityClass)
	{
		parent::__construct($context, $entityClass);
		$this->cache = new Cache($this->context->cacheStorage, 'Map');
	}

	/**
	 * Cache getter
	 * @return Nette\Caching\Cache
	 */
	public function getCache ()
	{
		return $this->cache;
	}

	/**
	 * Increase the number of issued orders for given clan
	 * @param Entities\Clan
	 * @param bool
	 * @throws InsufficientOrdersException
	 * @return void
	 */
	public function issueOrder (Entities\Clan $clan, $flush = TRUE)
	{
		if ($this->context->stats->orders->getAvailableOrders($clan) > 0) {
			$this->context->model->getOrdersService()->update($clan->orders, array(
				'issued' => $clan->orders->issued + 1
			), $flush);
		} else {
			throw new InsufficientOrdersException;
		}
	}

	public function addApplication (Entities\Clan $clan, Entities\Alliance $alliance, $flush = TRUE)
	{
		$clan->addApplication($alliance);
		if ($flush) {
			$this->entityManager->flush();
		}
	}

	/**
	 * Creates an object as the parent does
	 * In addition, it assigns N fields to the clan
	 * @param array
	 * @param bool
	 * @return Entities\Clan
	 */
	public function create ($values, $flush = TRUE)
	{

		$fieldService = $this->context->fieldService;
		$fieldRepository = $fieldService->getRepository();

		$mapSize = $this->context->params['game']['map']['size'];
		$playerDistance = $this->context->params['game']['map']['playerDistance'];
		$initialFieldsCount = $this->context->params['game']['map']['initialFieldsCount'];

		$S = $fieldRepository->findByCoords($mapSize/2 - 1, $mapSize/2);
		$map = $fieldRepository->getIndexedMap();

		$lastHqId = $this->cache->load('lastHqId');
		$lastHq = null;
		if ($lastHqId === null) {
			$lastHq =  $S;
		} else {
			$lastHq =  $fieldRepository->find($lastHqId);
		}

		Debugger::barDump($lastHq);
		$neutralHexagonsCenters = $fieldRepository->findNeutralHexagons($lastHq, $playerDistance, $map);
		//Debugger::barDump($neutralHexagonsCenters);
		$fieldRepository->sortByDistance($neutralHexagonsCenters, $S);


		$found = new ArraySet();
		foreach ($neutralHexagonsCenters as $center) {
			$finalized = false;

			$found = new ArraySet();
			$found->addElement($center->type, $center);

			$neighbours = $fieldRepository->getFieldNeighbours($center, 1, $map);
			foreach ($neighbours as $neighbour){

				if ($found->count() >= $initialFieldsCount){
					$finalized = true;
					break;
				}
				$found->addElement($neighbour->type, $neighbour);
			}

			if ($finalized){
				break;
			}
		}

		$foundArr = $found->toArray();
		$headq = array_pop($foundArr);

		$values['headquarters'] = $headq;
		$clan = parent::create($values, $flush);

		$this->context->model->getOrdersService()->create(array('owner' => $clan), FALSE);

		foreach ($found as $foundField) {
			$fieldService->update($foundField, array('owner' => $clan));
		}
		$this->cache->save('lastHqId', $headq->id);

		$now = new \DateTime();
		foreach ($this->context->rules->getAll('resource') as $type => $rule) {
			$this->context->model->getResourceService()->create(array(
				'clan' => $clan,
				'type' => lcfirst($type),
				'balance' => $rule->getInitialAmount(),
				'storage' => $this->context->params['game']['stats']['baseStorage'],
				'clearance' => $now
			), FALSE);
		}

		$this->getContext()->model->getResearchService()->create(array(
			'type' => 'militia',
			'owner' => $clan,
			'level' => 1
		), FALSE);

		$this->getContext()->model->getResearchService()->create(array(
			'type' => 'footman',
			'owner' => $clan,
			'level' => 1
		), FALSE);


		$this->entityManager->flush();
		$this->context->model->getUserService()->update($clan->user, array('activeClan' => $clan));
		return $clan;
	}

	/**
	 * Delete a persisted object and also set all its field neutral
	 * @param Entities\BaseEntity
	 * @param bool
	 * @return void
	 */
	public function delete ($object, $flush = TRUE)
	{
		$object->deleted = TRUE;
		if ($flush) {
			$this->entityManager->flush();
		}
	}

	public function deleteAll ($flush = TRUE){
		parent::deleteAll($flush);
		$this->cache->clean();
	}
}
