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
	* Finds 2-6 neighbours of field. When &$map defined, searches in $map instand of exectuting query 
	* @param Entities\Field
	* @param array of Entities\Field
	* @return array of Entities\Field
	*/
	public function getFieldNeighbours (Entities\Field $field, &$map = array())
	{
		$neighbours = array();
		$x = $field->getX();
		$y = $field->getY();
	
		if (count($map) > 0)
		{
			foreach ($map as $tmpField)
			{
				$tmpX = $tmpField->getX();
				$tmpY = $tmpField->getY();
				
				if(
						($tmpX == $x+1 and $tmpY == $y-1)
					or
						($tmpX == $x-1 and $tmpY == $y+1)
					or
						($tmpX == $x+1 and $tmpY == $y)
					or
						($tmpX == $x and $tmpY == $y+1)
					or
						($tmpX == $x and $tmpY == $y-1)
					or
						($tmpX == $x-1 and $tmpY == $y)
				)
				{
					$neighbours[] = $tmpField;
				}
			
			}

		
		}
		else
		{
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
			$neighbours = $qb->getQuery()->getResult();	
		}
		
		return $neighbours;	
		
		
	}
	

	
	/**
	* Finds the field by its coordinates
	* @param integer
	* @param integer
	* @return Entities\Field
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
	 * @return array of Entities\Field
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
	 * @return array of Entities\Field
	 */
	public function getVisibleFields ($userId, $depth)
	{
	
		$qb = $this->createQueryBuilder('f');
		$qb->where(
			$qb->expr()->eq('f.owner', $userId)
		);
		$ownerFields = $qb->getQuery()->getResult();
		$visibleFields = $this->findDeepNeighbours($depth, $ownerFields);
		
		return $visibleFields;
	}
	

	/**
	 * Finds fields which are <= $depth-th neighbour of given fields
	 * @param integer
	 * @param array of Entities\Field
	 * @param array of Entities\Field
	 * @param array of integer
	 * @param Entities\Field
	 * @param array of Entities\Field
	 * @param boolean
	 * @return array of Entities\Field
	 *
	 * TODO: @param mapSize (required for setting $depthArr zero value)
	 */
	protected function findDeepNeighbours($depth, $sources, &$deepNeigbours = array(), &$depthArr = array(), $startField = null, &$map = array(), $firstRun = true)
	{	
		if ($depth <= 0){
			return;
		}
		if ($firstRun){
			$depthArr = array();
			$deepNeigbours = array();
			
			for($i=0; $i<16; $i++){
				for($j=0; $j<16; $j++){
					$depthArr[$i][$j] = 0;			
				}
			}
			
			$map = $this->getMap();
			
			foreach ($sources as $source){
				$neighbours = $this->getFieldNeighbours($source, $map);
				$this->findDeepNeighbours($depth, null, $deepNeigbours, $depthArr, $source, $map, false);
			}
			return $deepNeigbours;
			
		}
		else{		
			if ($depth > $depthArr[$startField->getX()][$startField->getY()]){
			
				$depthArr[$startField->getX()][$startField->getY()] = $depth;
				$neighbours = $this->getFieldNeighbours($startField, $map);
				foreach ($neighbours as $neighbour){
					if (in_array($neighbour, $deepNeigbours, true) === false){
						$deepNeigbours[] = $neighbour;
					}
					$this->findDeepNeighbours($depth-1, null, $deepNeigbours, $depthArr, $neighbour, $map, false);
				}
			}
		}		
	}
	
	
	
	
	/**
	 * Finds centers of seven-fields-sized hexagons which are >= $depth far from the nearest field of any other player
	 * @param integer
	 * @param array of Entities\Field
	 * @param integer
	 * @param array of Entities\Field
	 * @param Entities\Field
	 * @param array of Entities\Field
	 * @param boolean
	 * @return void
	 *
	 * TODO: reduce area
	 *
	 */
	public function findNeutralHexagons($depth, &$foundCenters, $maxMapIndex, &$visitedFields = array(), $startField = null, &$map = array(), $firstRun = true){
	
		if($depth <= 0) {
			return true;
		}

	
		if ($firstRun) {
			$neutralFields = $this->findNeutralFields();
			$map = $this->getMap();
			foreach ($neutralFields as $neutralField) {
				$visitedFields[] = $neutralField;
				if ($neutralField->getX() <= 0 or $neutralField->getY() <= 0 or $neutralField->getX() >= $maxMapIndex or $neutralField->getY() >= $maxMapIndex or $neutralField->owner != null)
				{
					continue;
				}

				if ($this->findNeutralHexagons($depth, $foundCenters, $maxMapIndex, $visitedFields, $neutralField, $map, false)){
					$foundCenters[] = $neutralField;
				}
				
			}
		}
		else {

			if ($startField->getX() <= 0 or $startField->getY() <= 0 or $startField->getX() >= $maxMapIndex or $startField->getY() >= $maxMapIndex)
			{
				return false;
			}

			$neighbours = $this->getFieldNeighbours($startField, $map);
			if (count($neighbours) != 6)
			{
				return false;
			}
			
			foreach ($neighbours as $neighbour) {
				if ($neighbour->owner != null){
					return false;
				}
			}	
					
			foreach ($neighbours as $neighbour) {
				if (!$this->findNeutralHexagons($depth-1, $foundCenters, $maxMapIndex, $visitedFields, $neighbour, $map, false)){
					return false;
				}
			}	
			return true;
		

		}
	}
	
	
	/**
	 * Counts distance between field $a and $b
	 * @param Entities\Field
	 * @param Entities\Field
	 * @return integer
	 */
	public function calculateDistance($a, $b)
	{
		$sign = function ($x) {
			return $x == 0 ? 0 : (abs($x) / $x);
		};
		$dx = $b->getX() - $a->getX();
		$dy = $b->getY() - $a->getY();
		
		if ($sign($dx) === $sign($dy)) {
			return abs($dx) + abs($dy);
		}
		else {
			return max(abs($dx), abs($dy));
		}
	}
}
