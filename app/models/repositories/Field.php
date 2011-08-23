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
			if (array_search($source, $res, true) === false)	
			{
				$res[] = $source;
			}						
			
			$neighbours = $this->getFieldNeighbours($source);
			foreach ($neighbours as $neighbour){
				if (array_search($neighbour, $res, true) === false)	
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
	 * TODO: fix the "Call to a member function getX() on a non-object" error
	 *
	 * @param integer
	 * @param array of fields
	 * @param array of fields
	 * @param field
	 * @param boolean
	 * @return array of fields
	 */
	public function findNeutralHexagons($depth, &$foundCenters = array(), &$visitedFields = array(), $startField = null, $firstRun = true){
	
	//search->inarray
	
		if($depth <= 0) {
			return $foundCenters;
		}
	
		if ($firstRun) {
			$neutralFields = $this->findNeutralFields();
			foreach ($neutralFields as $neutralField) {
				$visitedFields[] = $neutralField;
				$this->findNeutralHexagons($depth, $foundCenters, $visitedFields, $neutralField, false);
			}
			
		}
		else {
			$neighbours = $this->getFieldNeighbours($startField); #tuto to zlobí ;)
			foreach ($neighbours as $neighbour) {
				if ($neighbour->owner != null){
					return;
				}
				if (array_search($neighbour, $visitedFields, true) === false) {
					$visitedFields[] = $neighbour;		
					
					$neigboursNextLvl = $this->getFieldNeighbours($neighbour);
					foreach ($neigboursNextLvl as $neigbourNextLvl) {
						if ($neigbourNextLvl->owner != null) {
							return;
						}
							
					}
						$foundCenters[] = $neighbour;
						$this->findNeutralHexagons($depth-1, $foundCenters, $visitedFields, $this->getFieldNeighbours($neighbour), false);
					
					
				
				}
			}
		}
	}
}