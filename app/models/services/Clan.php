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

	public function create ($values, $flush = TRUE)
	{
		$clan = parent::create($values, $flush);
		
		$x = 4;
		$y = 3;

		$fieldService = $this->context->fieldService;
		$fieldRepository = $fieldService->getRepository();
		
		$neutralFields[] = $fieldRepository->findByOwner("NULL");
Debugger::barDump($neutralFields);
$tmp[]='a';
$tmp[]='b';
Debugger::barDump($tmp);
		
		
		$found[] = $fieldRepository->findByCoords($x, $y);
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
		
		foreach ($found as $foundField)	// makes player the owner of $found fields
		{
			$fieldService->update($foundField, array('owner' => $clan));		
		}

		

//		Debugger::barDump($neighbours);
		//return sth; ?

	}

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
