<?php
namespace Services;
use Nette\Diagnostics\Debugger;
/**
 * Clan service class
 * @author Petr Bělohlávek
 */
class Clan extends BaseService {

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

		$S = $fieldRepository->findByCoords($mapSize/2, $mapSize/2 - 1);
		$map = $fieldRepository->getIndexedMap();

		$outline = 0;
		$found = array();

		while (count($found) < $initialFieldsCount){

			$neutralHexagons = $fieldRepository->findNeutralHexagons($outline, $playerDistance, $map);

			if(count($neutralHexagons) <= 0){
				$outline++;
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
				$neighbours = $fieldRepository->getFieldNeighbours($neutralHexagon, 1,$map);

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
			$outline++;
		}

		$clan = parent::create($values, $flush);
		foreach ($found as $foundField){
			$fieldService->update($foundField, array('owner' => $clan));
		}
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
		foreach ($object->getFields() as $field)
		{
			$field->setOwner(NULL);
			$this->entityManager->persist($field);
		}
		parent::delete($object, $flush);
	}

}
