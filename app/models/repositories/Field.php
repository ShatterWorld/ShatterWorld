<?php
namespace Repositories;
use Nette;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;


class Field extends BaseRepository {
	/**
	* Returns the whole map as an array
	* @Deprecated (getIndexedMap)
	* @return array of Entities\Field
	*/
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
	* Returns the map indexed by coordinates
	* @return array of arrays of Entities\Field
	*/
	public function getIndexedMap ()
	{
		$result = array();
		foreach ($this->findAll() as $field) {
			$x = $field->getX();
			$y = $field->getY();
			if (!isset($result[$x])) {
				$result[$x] = array();
			}
			$result[$x][$y] = $field;
		}
		return $result;
	}

	/**
	* Finds 2-6 neighbours of field. If $map is given, it is searched instead of fetching the map from the database
	* @param Entities\Field
	* @param array of Entities\Field
	* @return array of Entities\Field
	*/
	public function getFieldNeighbours (Entities\Field $field, $depth = 1, &$map = array())
	{
		$neighbours = array();

		if ($depth > 1){
			if (count($map) <= 0){
				$map = $this->getIndexedMap();
			}

			for($d = 1; $d <= $depth; $d++){
				$circuitNeighbours = $this->findCircuit($field, $d, $map);
				foreach ($circuitNeighbours as $circuitNeighbour){
					$neighbours[] = $circuitNeighbour;
				}
			}

			return $neighbours;
		}

		$x = $field->getX();
		$y = $field->getY();

		if ($map)
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
	* Finds a field by its coordinates (If $map is given, iterates through it instand of quering)
	* @param integer
	* @param integer
	* @param array of Entities\Field
	* @return Entities\Field
	*
	* TODO: needs boundaries check
	*
	*/
	public function findByCoords ($x, $y, &$map = array())
	{
		if(count($map) > 0){
			return $map[$x][$y];
		}

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
		foreach($ownerFields as $ownerField){
			$neighbours = $this->getFieldNeighbours($ownerField, $depth);

			foreach($neighbours as $neighbour){
				if(array_search($neighbour, $visibleFields, true) === false){
					$visibleFields[] = $neighbour;
				}
			}
		}

		return $visibleFields;
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
	public function findNeutralHexagons($middleLine, $playerDistance, $tolerance, &$map = array()){
		$mapSize = 50;

		if(count($map) <= 0){
			$map = $this->getIndexedMap();
		}

		$S = $this->findByCoords($mapSize/2, $mapSize/2 - 1);

		$foundCenters = array();
		/*$hasOwner = array();

		for($i=0;$i<$mapSize;i++){
			for($j=0;$j<$mapSize;j++){
				$hasOwner = null;
			}
		}*/


		for($d = $middleLine - $tolerance; $d <= $middleLine + $tolerance; $d++){
			$circuit = $this->findCircuit($S, $d, $map);
			$noOwners = 0;
			foreach($circuit as $field){
				if($field->owner == null){
					$neighbours = $this->getFieldNeighbours($field, $playerDistance, $map);
					$add = true;

					foreach($neighbours as $neighbour){
						if($neighbour->owner != null){
							$add = false;
							break;
						}
					}
					if ($add){
						$foundCenters[] = $field;
					}
				}
			}
		}



		return $foundCenters;

	}
/*	public function findNeutralHexagons($depth, &$foundCenters, $maxMapIndex, &$visitedFields = array(), $startField = null, &$map = array(), $firstRun = true){

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
*/

	/**
	 * Counts distance between field $a and $b
	 * @param Entities\Field
	 * @param Entities\Field
	 * @return integer
	 */
	public function calculateDistance ($a, $b)
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


	/**
	 * Finds zone of fields which are settled in hexagon (center $S, radius $r)
	 * @param Entities\Field
	 * @param integer
	 * @param Entities\Field
	 * @return array of Entities\Field
	 */
	public function findCircuit ($S, $r, &$map = array())
	{
		if(count($map) <= 0){
			$map = $this->getIndexedMap();
		}

		if($r == 1){
			return $this->getFieldNeighbours($S);
		}

		$x = $S->getX();
		$y = $S->getY();

		$coords = array(
			'north' => array('x' => $x + $r, 'y' => $y - $r),
			'south' => array('x' => $x - $r, 'y' => $y + $r),
			'north-west' => array('x' => $x, 'y' => $y - $r),
			'north-east' => array('x' => $x + $r, 'y' => $y),
			'south-west' => array('x' => $x - $r, 'y' => $y),
			'south-east' => array('x' => $x, 'y' => $y + $r)
		);

		$vertexes = array();
		foreach($coords as $name => $coord){
			$vertexes[$name] = $this->findByCoords($coord['x'], $coord['y'], $map);
		}

		$circuit = array();

		$tmpX = $vertexes['north']->getX();
		$tmpY = $vertexes['north']->getY();
		$targetX = $vertexes['north-east']->getX();
		$targetY = $vertexes['north-east']->getY();
		while($tmpY < $targetY){
			$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			$tmpY++;
		}

		$tmpX = $vertexes['north-east']->getX();
		$tmpY = $vertexes['north-east']->getY();
		$targetX = $vertexes['south-east']->getX();
		$targetY = $vertexes['south-east']->getY();
		while($tmpY < $targetY and $tmpX > $targetX){
			$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			$tmpY++;
			$tmpX--;
		}

		$tmpX = $vertexes['south-east']->getX();
		$tmpY = $vertexes['south-east']->getY();
		$targetX = $vertexes['south']->getX();
		$targetY = $vertexes['south']->getY();
		while($tmpX > $targetX){
			$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			$tmpX--;
		}

		$tmpX = $vertexes['south']->getX();
		$tmpY = $vertexes['south']->getY();
		$targetX = $vertexes['south-west']->getX();
		$targetY = $vertexes['south-west']->getY();
		while($tmpY > $targetY){
			$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			$tmpY--;
		}

		$tmpX = $vertexes['south-west']->getX();
		$tmpY = $vertexes['south-west']->getY();
		$targetX = $vertexes['north-west']->getX();
		$targetY = $vertexes['north-west']->getY();
		while($tmpY > $targetY and $tmpX < $targetX){
			$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			$tmpY--;
			$tmpX++;
		}

		$tmpX = $vertexes['north-west']->getX();
		$tmpY = $vertexes['north-west']->getY();
		$targetX = $vertexes['north']->getX();
		$targetY = $vertexes['north']->getY();
		while($tmpX < $targetX){
			$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			$tmpX++;
		}

		return $circuit;
	}
}
