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
		$initialFieldsCount = $this->context->params['game']['map']['initialFieldsCount'];
		$candidate = $this->context->map->getHighestRankedField();
		$headq = $this->context->model->fieldRepository->findByCoords($candidate['x'], $candidate['y']);
		$territory = array($headq);
		$fieldCount = 1;
		foreach ($this->context->model->fieldRepository->findCircuit($headq, 1) as $field) {
			$territory[] = $field;
			if (count($territory) >= $initialFieldsCount) {
				break;
			}
		}
		$values['headquarters'] = $headq;
		$clan = parent::create($values, $flush);

		$this->context->model->getOrdersService()->create(array('owner' => $clan), FALSE);

		$this->context->map->open();

		$initScore = array();

		$increment = 0;
		foreach ($territory as $field) {
			$fieldService->update($field, array('owner' => $clan));
			$this->context->map->occupyField($field->x, $field->y, 1);
			$increment += $this->context->rules->get('field', $field->type)->getValue();
		}
		$initScore['territory'] = $increment;

		$this->context->map->close();
		$this->cache->save('lastHqId', $headq->id);

		$initial = $this->context->params['game']['initial'];
		$now = new \DateTime();



		$increment = 0;
		foreach ($initial['resources'] as $resource => $balance) {
			$object = $this->context->model->getResourceService()->create(array(
				'clan' => $clan,
				'type' => $resource,
				'balance' => $balance,
				'storage' => $this->context->params['game']['stats']['baseStorage'],
				'clearance' => $now
			), FALSE);
		}
		$initScore['production'] = $increment;

		$increment = 0;
		foreach ($initial['units'] as $unit => $count) {
			$this->context->model->unitService->create(array(
				'type' => $unit,
				'count' => $count,
				'location' => $headq,
				'owner' => $clan
			), FALSE);
			$increment += $this->context->rules->get('unit', $unit)->getValue() * $count;
		}
		$initScore['unit'] = $increment;

		$increment = 0;
		foreach ($initial['researches'] as $research => $level) {
			$this->context->model->getResearchService()->create(array(
				'type' => $research,
				'owner' => $clan,
				'level' => $level
			), FALSE);
			$increment += $this->context->rules->get('research', $research)->getValue($level);
		}
		$initScore['research'] = $increment;

		$increment = 0;
		foreach ($initial['quests'] as $quest) {
			$this->context->model->questService->create(array(
				'type' => $quest,
				'owner' => $clan,
				'start' => $now
			), FALSE);
		}
		$initScore['quest'] = $increment;

		$initScore['facility'] = $this->context->rules->get('facility', 'headquarters')->getValue(1);

		foreach ($this->context->params['game']['score'] as $type) {
			$this->context->model->scoreService->create(array(
				'type' => $type,
				'owner' => $clan,
				'value' => $initScore[$type]
			), TRUE);
		}


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
