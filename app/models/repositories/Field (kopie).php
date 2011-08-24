<?php
namespace Repositories;
use Nette;
use Doctrine;
use Entities;
use Doctrine\Common\Collections\ArrayCollection;

use Nette\Diagnostics\Debugger;


class Field extends Doctrine\ORM\EntityRepository {
	public function getMap ()
	{
		$data = $this->createQueryBuilder('f')
			->addOrderBy('f.coordX')
			->addOrderBy('f.coordY')
			->getQuery()
			->getResult();
		return $data;
	}
	
	
	/**
	* Finds 2-6 neighbours of field
	* @param Entities\Field
	* @return array of field
	*/
	public function getFieldNeighbours (Entities\Field $field)
	{
		$x = $field->getX();
		$y = $field->getY();
	
		$qb = $this->createQueryBuilder('f');
		$qb->where($qb->expr()->orX(
			$qb->expr()->andX(# north
				$qb->expr()->eq('f.coordX', $x+1),
				$qb->expr()->eq('f.coordY', $y-1)
			),
			$qb->expr()->andX(# south
				$qb->expr()->eq('f.coordX', $x-1),
				$qb->expr()->eq('f.coordY', $y+1)
			),
			$qb->expr()->andX(# north-east
				$qb->expr()->eq('f.coordX', $x+1),
				$qb->expr()->eq('f.coordY', $y)
			),
			$qb->expr()->andX(# south-east
				$qb->expr()->eq('f.coordX', $x),
				$qb->expr()->eq('f.coordY', $y+1)
			),
			$qb->expr()->andX(# north-west
				$qb->expr()->eq('f.coordX', $x),
				$qb->expr()->eq('f.coordY', $y-1)
			),
			$qb->expr()->andX(# south-east
				$qb->expr()->eq('f.coordX', $x-1),
				$qb->expr()->eq('f.coordY', $y)
			)
		));
		return $qb->getQuery()->getResult();	
	}
	

	
	/**
	* Finds the field by its coordinates
	* @param integer
	* @param integer
	* @return field
	*/
	public function findByCoords ($x, $y)
	{
		$qb = $this->createQueryBuilder('f');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('f.coordX', $x),
			$qb->expr()->eq('f.coordY', $y)
		));
			
		return $qb->getQuery()->getSingleResult();			
	}
	
	/**
	 * Finds fields with no owner
	 * @return array
	 */
	public function findNeutralFields ()
	{
		$qb = $this->createQueryBuilder('f');
		$qb->where(
			$qb->expr()->isNull('f.owner')
		);
		return $qb->getQuery()->getResult();
	}

	/**
	 * Finds fields which are visible for the player
	 * @param integer
	 * @param integer
	 * @return array
	 */
	public function getVisibleFields ($userId, $depth)
	{
	
		$qb = $this->createQueryBuilder('f');
		$qb->where(
			$qb->expr()->eq('f.owner', $userId)
		);
		$ownerFields = $qb->getQuery()->getResult();
		
		$visibleFields = array();
		$this->findDeepdNeighbours($visibleFields, $ownerFields, $depth);
		
		return $visibleFields;
	}
	

	/**
	 * Finds fields which are >= $depth-th neighbour of given fields and saves them to given array
	 * @param array of fields
	 * @param array of fields
	 * @param integer
	 * @return void
	 */
	protected function findDeepdNeighbours(&$res, $sources, $depth)
	{
		if ($depth <= 0)
		{
			return;
		}
		foreach ($sources as $source)
		{
			if (in_array($source, $res, true) === false)	
			{
				$res[] = $source;
			}						
			
			$neighbours = $this->getFieldNeighbours($source);
			foreach ($neighbours as $neighbour){
				if (in_array($neighbour, $res, true) === false)	
				{
					$res[] = $neighbour;	
					$this->findDeepdNeighbours($res, $this->getFieldNeighbours($neighbour), $depth-1);
				}						
			}		
		}	
	
	}
	
	
	
	
	/**
	 * Finds centers of seven-fields-sized hexagons which are >= $depth far from the nearest field of any other player
	 * 
	 * TODO: cache map (multtiple calling of getFieldNeighbours)
	 *
	 * @param integer
	 * @param array of fields
	 * @param array of fields
	 * @param field
	 * @param boolean
	 * @return array of fields
	 */
	public function findNeutralHexagons($depth, &$foundCenters, $mapSize, &$visitedFields = array(), $startField = null, $firstRun = true){
	
		if($depth <= 0) {
			return;
		}
	
		if ($firstRun) {
			$neutralFields = $this->findNeutralFields();
			foreach ($neutralFields as $neutralField) {
				$visitedFields[] = $neutralField;
				/*if ($neutralField->getX() == 0 or $neutralField->getY() == 0 or $neutralField->getX() == $mapSize or $neutralField->getY() == $mapSize)
				{
					break;
				}*/

				$this->findNeutralHexagons($depth, $foundCenters, $mapSize, $visitedFields, $neutralField, false);
			}
			
		}
		else {
			if ($startField->getX() == 0 or $startField->getY() == 0 or $startField->getX() == $mapSize or $startField->getY() == $mapSize)
			{
				return;
			}

			$neighbours = $this->getFieldNeighbours($startField);
			if (count($neighbours) < 6)
			{
				return;
			}
			foreach ($neighbours as $neighbour) {
				if ($neighbour->owner != null){
					return;
				}
				
				
				if (in_array($neighbour, $visitedFields, true) === false) {
					$visitedFields[] = $neighbour;		
					
					$neigboursNextLvl = $this->getFieldNeighbours($neighbour);
					foreach ($neigboursNextLvl as $neigbourNextLvl) {
						if ($neigbourNextLvl->owner != null) {
							return;
						}
							
					}
					$foundCenters[] = $neighbour;
					$this->findNeutralHexagons($depth-1, $foundCenters, $mapSize, $visitedFields, $neighbour, false);
														
				}
			}
		}
	}
}
