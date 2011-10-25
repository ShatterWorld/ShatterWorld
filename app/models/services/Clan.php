<?php
namespace Services;
use Nette\Caching\Cache;
use Nette\Diagnostics\Debugger;
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
		$toleration = $this->context->params['game']['map']['toleration'];

		$S = $fieldRepository->findByCoords($mapSize/2 - 1, $mapSize/2);
		$map = $fieldRepository->getIndexedMap();

		$outline = $this->cache->load('outline');
		if ($outline === null || $outline - $toleration < 0){
			$level = 0;
		}
		else{
			$level = $outline - $toleration;
		}

		$found = array();

		while (count($found) < $initialFieldsCount){

			$neutralHexagons = $fieldRepository->findNeutralHexagons($level, $playerDistance, $S, $map);

			if(count($neutralHexagons) <= 0){
				$level++;
				continue;
			}

			usort($neutralHexagons, function ($a, $b) use ($fieldRepository, $S){
				$dA = $fieldRepository->calculateDistance($S, $a);
				$dB = $fieldRepository->calculateDistance($S, $b);

				if ($dA == $dB) {
					return 0;
				}
				return ($dA < $dB) ? -1 : 1;
			});

			foreach ($neutralHexagons as $neutralHexagon){
				$neighbours = $fieldRepository->getFieldNeighbours($neutralHexagon, 1, $map);

				$finalized = false;
				$found = array();
				$found[] = $neutralHexagon;

				foreach ($neighbours as $neighbour){

					if (count($found) >= $initialFieldsCount){
						$finalized = true;
						break;
					}

					$search = false;
					foreach ($found as $foundField){
						if($neighbour->type == $foundField->type){
							$search = true;
							break;
						}
					}

					if ($search){
						continue;
					}

					$found[] = $neighbour;
				}

				if ($finalized){
					break;
				}
			}
			$level++;
		}
		$this->cache->save('outline', $level/* + $toleration*/);

		$maxNeighbours = 0;
		$headq = null;
		$clan = parent::create($values, $flush);
		foreach ($found as $foundField){
			$neighboursNmb = count($fieldRepository->getFieldNeighbours($foundField, 1, $map));
			if ( $neighboursNmb > $maxNeighbours){
				$maxNeighbours = $neighboursNmb;
				$headq = $foundField;
			}
			$fieldService->update($foundField, array('owner' => $clan));
		}
		$this->update($clan, array('headquarters' => $headq));
		$fieldService->update($headq, array('facility' => 'headquarters'));
		
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
