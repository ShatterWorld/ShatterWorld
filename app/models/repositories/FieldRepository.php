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
			
		return $qb->getQuery()->getResult();			
	}
}
