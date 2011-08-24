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
	const N = 3; 

	/**
	* Creates an object as parent does
	* In addition, it assigns N fields to the clan
	* @param array
	* @param bool
	* @return object
	*/
	public function create ($values, $flush = TRUE)
	{
		$clan = parent::create($values, $flush);
		
		$x = 4;
		$y = 3;

		$fieldService = $this->context->fieldService;
		$fieldRepository = $fieldService->getRepository();
		
		$mapSize = 8;
		$maxMapIndex = $mapSize - 1;
		
		$S = $fieldRepository->findByCoords($mapSize/2, $mapSize/2 + 1);
		$neutralHexagons = array();
		$fieldRepository->findNeutralHexagons(1, $neutralHexagons, $maxMapIndex);
		Debugger::barDump($neutralHexagons);
		
		
		
		
		//finds the one which is closest to the center
		$minDist = 99999999999;
		$minField;
		foreach ($neutralHexagons as $neutralHexagon)
		{
			$dist = $fieldRepository->calculateDistance($S, $neutralHexagon);
			if ($dist < $minDist)
			{
				$minDist = $dist;
				$minField = $neutralHexagon;
			}
		}

		$found[] = $minField;		
		
//-------------------		
		//$found[] = $fieldRepository->findByCoords($x, $y);
		$neighbours = $fieldRepository->getFieldNeighbours($found[0]);
//Debugger::barDump($neighbours);


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
		
		foreach ($found as $foundField)	// makes player the owner of $found fields
		{
			$fieldService->update($foundField, array('owner' => $clan));		
		}

		

//		Debugger::barDump($neighbours);
		return clan; 

	}

	/**
	 * Delete a persisted object and also set all its field neutral
	 * @param Entities\BaseEntity
	 * @param bool
	 * @return void
	 */
	public function delete ($object, $flush = TRUE)
	{

		$fields = $object->getFields();
		
		foreach ($fields as $field)
		{
			$field->setOwner(NULL);
			$this->entityManager->persist($field);	
		}


		parent::delete($object, $flush);


	}
	
}
