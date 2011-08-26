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
		//$neutralHexagons = array();
		//$fieldRepository->findNeutralHexagons($playerDistance, $neutralHexagons, $maxMapIndex);

		//TODO: better everything ;)

		$middleLine = 20;
		$neutralHexagons = $fieldRepository->findNeutralHexagons($middleLine, $playerDistance, 5);



		$minDist = 99999999999;
		$minField;


		foreach ($neutralHexagons as $neutralHexagon){
			$dist = $fieldRepository->calculateDistance($S, $neutralHexagon);
			if ($dist < $minDist){
				$minDist = $dist;
				$minField = $neutralHexagon;
			}
		}



		$found[] = $minField;

// other fields search improvement required


		//$found[] = $fieldRepository->findByCoords($x, $y);
		$neighbours = $fieldRepository->getFieldNeighbours($found[0]);


		foreach ($neighbours as $neight) // founds appropriate fields
		{

			if (count($found) >= self::N)
			{
				break;
			}

			$cont = true;


			foreach ($found as $foundField)
			{
				if($neight->type == $foundField->type)
				{
					$cont = false;
					break;
				}

			}

			if (!$cont){
				continue;
			}

			$found[] = $neight;


		}

		$clan = parent::create($values, $flush);
		foreach ($found as $foundField){	// makes player the owner of $found fields
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
