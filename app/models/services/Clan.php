<?php
namespace Services;
use Nette\Caching\Cache;
use Nette\Diagnostics\Debugger;
use ArraySet;
/**
 * Clan service class
 * @author Petr Bělohlávek
 */
class Clan extends BaseService {


	/** @var Nette\Caching\Cache*/
	protected $cache;


	public function __construct ($context, $entityClass)
	{
		parent::__construct($context, $entityClass);
		$this->cache = new Cache($this->context->cacheStorage, 'Map');
	}

	/**
	* Creates an object as parent does
	* In addition, it assigns N fields to the clan
	* @param array
	* @param bool
	* @return object
	*
	* TODO: outline caching and incrementing
	*
	*/
	public function create ($values, $flush = TRUE)
	{

		$fieldService = $this->context->fieldService;
		$fieldRepository = $fieldService->getRepository();

		$mapSize = $this->context->params['game']['map']['size'];
		$playerDistance = $this->context->params['game']['map']['playerDistance'];
		$initialFieldsCount = $this->context->params['game']['map']['initialFieldsCount'];
		//$toleration = $this->context->params['game']['map']['toleration'];

		$S = $fieldRepository->findByCoords($mapSize/2 - 1, $mapSize/2);
		$map = $fieldRepository->getIndexedMap();

		/*$outline = $this->cache->load('outline');
		if ($outline === null || $outline - $toleration < 0){
			$level = 0;
		}
		else{
			$level = $outline - $toleration;
		}*/

		$lastHqId = $this->cache->load('lastHqId');
		$lastHq = null;
		if ($lastHqId === null){
			$lastHq =  $S;
		}
		else{
			$lastHq =  $fieldRepository->find($lastHqId);
		}

		//Debugger::barDump($S);
		//Debugger::barDump($lastHq);


		$neutralHexagonsCenters = $fieldRepository->findNeutralHexagons($lastHq, $playerDistance, $map);

		//Debugger::barDump($neutralHexagonsCenters);
		$fieldRepository->sortByDistance($neutralHexagonsCenters, $S);


		$found = new ArraySet();
		foreach ($neutralHexagonsCenters as $center){

			$neighbours = $fieldRepository->getFieldNeighbours($center, 1, $map);
			$finalized = false;

			$found = new ArraySet();
			$found->addElement($center->type, $center);

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

		//Debugger::barDump($found);

		$maxNeighbours = 0;
		$headq = null;
		foreach ($found as $foundField){
			$headq = $foundField;
			break;
		}
		//Debugger::barDump($headq);

		$values['headquarters'] = $headq;
		//Debugger::barDump($values);
		$clan = parent::create($values, $flush);
		Debugger::barDump($clan);
		$fieldService->update($headq, array('facility' => 'headquarters'));

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
		$this->entityManager->flush();
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
		$object->setAllianceApplication(NULL);
		if ($object->alliance && $object->alliance->leader === $object) {
			$this->context->model->getAllianceService()->delete($object->alliance);
		}
		foreach ($this->context->model->getOfferRepository()->findByOwner($object->id) as $offer) {
			$this->context->model->getOfferService()->delete($offer);
		}
		foreach ($object->getFields() as $field) {
			$field->setOwner(NULL);
		}
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
