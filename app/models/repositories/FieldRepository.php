<?php
namespace Repositories;
use Nette;
use Doctrine;

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
		$qb->where($qb->expr()->or(
			$qb->expr()->and(# north
				$qb->expr()->eq('coordX', x+1),
				$qb->expr()->eq('coordY', y-1)
			),
			$qb->expr()->and(# south
				$qb->expr()->eq('coordX', x-1),
				$qb->expr()->eq('coordY', y+1)
			),
			$qb->expr()->and(# north-east
				$qb->expr()->eq('coordX', x+1),
				$qb->expr()->eq('coordY', y)
			),
			$qb->expr()->and(# south-east
				$qb->expr()->eq('coordX', x),
				$qb->expr()->eq('coordY', y+1)
			),
			$qb->expr()->and(# north-west
				$qb->expr()->eq('coordX', x),
				$qb->expr()->eq('coordY', y-1)
			),
			$qb->expr()->and(# south-east
				$qb->expr()->eq('coordX', x-1),
				$qb->expr()->eq('coordY', y)
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
			$qb->where($qb->expr()->and(
				$qb->expr()->eq('coordX', x),
				$qb->expr()->eq('coordY', y)
			));
			
		return $qb->getQuery()->getResult();			
	}
}
