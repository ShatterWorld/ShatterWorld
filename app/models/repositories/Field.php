<?php
namespace Repositories;
use Nette;
use Doctrine;
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
	public function getFieldNeighbours ($field)
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
	 * @return array
	 */
	protected function findDeepdNeighbours(&$res, $sources, $depth)
	{
		if ($depth <= 0)
		{
			return;
		}
		foreach ($sources as $source)
		{
			if (array_search($source, $res) === false)	
			{
				$res[] = $source;
			}						
			
			$neighbours = $this->getFieldNeighbours($source);
			foreach ($neighbours as $neighbour){
				if (array_search($neighbour, $res) === false)	
				{
					$res[] = $neighbour;	
					$this->findDeepdNeighbours($res, $this->getFieldNeighbours($neighbour), $depth-1);
				}						
			}		
		}	
	
	}
	
}
















