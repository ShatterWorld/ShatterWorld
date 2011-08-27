<?php
namespace Services;
use Nette\Diagnostics\Debugger;
/**
 * Clan service class
 * @author Petr Bělohlávek
 */
class Clan extends BaseService {

	/*
	* The total number of filed which player is given
	*/
	const N = 3;//$this->context->params['game']['map']['initialFieldsCount'];

	/**
	* Creates an object as parent does
	* In addition, it assigns N fields to the clan
	* @param array
	* @param bool
	* @return object
	*/
	public function create ($values, $flush = TRUE)
	{

		$fieldService = $this->context->fieldService;
		$fieldRepository = $fieldService->getRepository();

		$mapSize = $this->context->params['game']['map']['size'];
		$playerDistance = $this->context->params['game']['map']['playerDistance'];
		$maxMapIndex = $mapSize - 1;

		$S = $fieldRepository->findByCoords($mapSize/2, $mapSize/2 - 1);

		//TODO: better everything ;)




		$middleLine = 3;
		$tolerance = 3;
		$found = array();

		while (count($found) < self::N){

			$neutralHexagons = $fieldRepository->findNeutralHexagons($middleLine, $playerDistance, $tolerance, $mapSize);

			if(count($neutralHexagons) <= 0){
				$middleLine++;
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
				$neighbours = $fieldRepository->getFieldNeighbours($neutralHexagon);

				$finalized = false;
				$found = array();
				$found[] = $neutralHexagon;

				foreach ($neighbours as $neighbour){

					if (count($found) >= self::N){
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
			$middleLine++;

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
