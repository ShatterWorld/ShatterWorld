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
		
		$visibleFields = array();
		$this->findDeepdNeighbours($visibleFields, $ownerFields, $depth);
		
		return $visibleFields;
	}
	

	/**
	 * Finds fields which are >= $depth-th neighbour of given fields and saves them to given array
	 * @param array of Entities\Field
	 * @param array of Entities\Field
	 * @param integer
	 * @return void
	 */
	protected function findDeepdNeighbours(&$res, $sources, $depth, &$map = array(), $firstRun = true)
	{	
		/*if ($depth <= 0)
		{
			return;
		}
		
		if ($firstRun)
		{
			$map = $this->getMap();
		}
		else
		{
			foreach ($sources as $source)
			{
				if (in_array($source, $res, true) === false)	
				{
					$res[] = $source;
				}						
			
				$neighbours = $this->getFieldNeighbours($source, $map); // add $map !!!!
				foreach ($neighbours as $neighbour){
					if (in_array($neighbour, $res, true) === false)	
					{
						$res[] = $neighbour;	
						$this->findDeepdNeighbours($res, $this->getFieldNeighbours($neighbour, $map), $depth-1, $map, false);// add $map !!!!
					}						
				}		
			}	
		}*/
		
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
	 * @param integer
	 * @param array of Entities\Field
	 * @param integer
	 * @param array of Entities\Field
	 * @param Entities\Field
	 * @param array of Entities\Field
	 * @param boolean
	 * @return void
	 */
	public function findNeutralHexagons($depth, &$foundCenters, $maxMapIndex, &$visitedFields = array(), $startField = null, &$map = array(), $firstRun = true){
	
		if($depth <= 0) {
			return;
		}
	
		if ($firstRun) {
			$neutralFields = $this->findNeutralFields();
			$map = $this->getMap();
			foreach ($neutralFields as $neutralField) {
				$visitedFields[] = $neutralField;
				if ($neutralField->getX() <= 0 or $neutralField->getY() <= 0 or $neutralField->getX() >= $maxMapIndex or $neutralField->getY() >= $maxMapIndex)
				{
					continue;
				}

				$this->findNeutralHexagons($depth, $foundCenters, $maxMapIndex, $visitedFields, $neutralField, $map, false);
			}
			
		}
		else {
			if ($startField->getX() <= 0 or $startField->getY() <= 0 or $startField->getX() >= $maxMapIndex or $startField->getY() >= $maxMapIndex)
			{
				return;
			}

			$neighbours = $this->getFieldNeighbours($startField, $map);
			if (count($neighbours) != 6)
			{
				return;
			}
			
			foreach ($neighbours as $neighbour) {
				if ($neighbour->owner != null){
					return;
				}
			}	
					
			$foundCenters[] = $startField;
			$visitedFields[] = $startField;		
			foreach ($neighbours as $neighbour) {
				$this->findNeutralHexagons($depth-1, $foundCenters, $maxMapIndex, $visitedFields, $neighbour, $map, false);			
			}			
		}
	}
	
	
	/**
	 * Counts distance between field $a and $b
	 * @param Entities\Field
	 * @param Entities\Field
	 * @return integer
	 */
	public function countDistance($a, $b)
	{
		$sign = function ($x) {
			return $x == 0 ? 0 : (abs($x) / $x);
		};
		$dx = $b->getX() - $a->getX();
		$dy = $b->getY() - $a->getY();
		
		if ($sign($dx) = $sign($dy)) {
			return abs($dx) + abs($dy);
		}
		else {
			return max(abs($dx), abs($dy));
		}
	}
	
	
	
}
