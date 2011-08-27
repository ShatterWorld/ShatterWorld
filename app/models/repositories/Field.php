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

			for($d = 1; $d <= $depth; $d++){
				$circuitNeighbours = $this->findCircuit($field, $d, $map);
				foreach ($circuitNeighbours as $circuitNeighbour){
					$neighbours[] = $circuitNeighbour;
				}
			}
		}
		else if (count($map) > 0){
			$x = $field->getX();
			$y = $field->getY();

			if (isset($map[$x+1][$y-1])){
				$neighbours[] = $map[$x+1][$y-1];
			}
			if (isset($map[$x+1][$y])){
				$neighbours[] = $map[$x+1][$y];
			}
			if (isset($map[$x-1][$y+1])){
				$neighbours[] = $map[$x-1][$y+1];
			}
			if (isset($map[$x-1][$y])){
				$neighbours[] = $map[$x-1][$y];
			}
			if (isset($map[$x][$y+1])){
				$neighbours[] = $map[$x][$y+1];
			}
			if (isset($map[$x][$y-1])){
				$neighbours[] = $map[$x][$y-1];
			}

		}
		else{
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
		$mapSize = $this->context->params['game']['map']['size'];
		if ($x >= $mapSize || $y >= $mapSize || $x < 0 || $y < 0) {
			throw new InvalidCoordinatesException;
		}
		if (count($map) > 0){
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
	 * Finds centers of seven-fields-sized hexagons which are located $outline from the center of the map. The hexagons are chosen only if $playerDistance fields around are neutral.
	 * @param integer
	 * @param integer
	 * @param Entities\Field
	 * @param array of Entities\Field
	 * @return array of Entities\Field
	 *
	 * TODO: $visitedFields (maybe not necessary)
	 *
	 */
	public function findNeutralHexagons($outline, $playerDistance, $S = null, &$map = array()){

		if ($S == null){
			$mapSize = $this->context->params['game']['map']['size'];
			$S = $this->findByCoords($mapSize/2 - 1, $mapSize/2);
		}

		$foundCenters = array();
		$circuit = $this->findCircuit($S, $outline, $map);

		foreach($circuit as $field){
			if($field->owner == null){
				$neighbours = $this->getFieldNeighbours($field, $playerDistance + 1, $map);
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

		return $foundCenters;

	}

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
	 * Finds zone of fields which are settled in hexagon ABCDEF (center $S, radius $r i.e. |SA|==|SB|==$r)
	 * @param Entities\Field
	 * @param integer
	 * @param Entities\Field
	 * @return array of Entities\Field
	 */
	public function findCircuit ($S, $r, &$map = array())
	{
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

		$circuit = array();
		foreach($coords as $coord){
			try{
				$vertexes[] = $this->findByCoords($coord['x'], $coord['y'], $map);
			}
			catch(InvalidCoordinatesException $e){
				continue;
			}
		}


		$tmpX = $coords['north']['x'];
		$tmpY = $coords['north']['y'];
		$targetX = $coords['north-east']['x'];
		$targetY = $coords['north-east']['y'];
		while($tmpY < $targetY){
			try{
				$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			}
			catch(InvalidCoordinatesException $e){}
			$tmpY++;

		}

		$tmpX = $coords['north-east']['x'];
		$tmpY = $coords['north-east']['y'];
		$targetX = $coords['south-east']['x'];
		$targetY = $coords['south-east']['y'];
		while($tmpY < $targetY and $tmpX > $targetX){
			try{
				$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			}
			catch(InvalidCoordinatesException $e){}
			$tmpY++;
			$tmpX--;
		}

		$tmpX = $coords['south-east']['x'];
		$tmpY = $coords['south-east']['y'];
		$targetX = $coords['south']['x'];
		$targetY = $coords['south']['y'];
		while($tmpX > $targetX){
			try{
				$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			}
			catch(InvalidCoordinatesException $e){}
			$tmpX--;
		}

		$tmpX = $coords['south']['x'];
		$tmpY = $coords['south']['y'];
		$targetX = $coords['south-west']['x'];
		$targetY = $coords['south-west']['y'];
		while($tmpY > $targetY){
			try{
				$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			}
			catch(InvalidCoordinatesException $e){}
			$tmpY--;
		}

		$tmpX = $coords['south-west']['x'];
		$tmpY = $coords['south-west']['y'];
		$targetX = $coords['north-west']['x'];
		$targetY = $coords['north-west']['y'];
		while($tmpY > $targetY and $tmpX < $targetX){
			try{
				$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			}
			catch(InvalidCoordinatesException $e){}
			$tmpY--;
			$tmpX++;
		}

		$tmpX = $coords['north-west']['x'];
		$tmpY = $coords['north-west']['y'];
		$targetX = $coords['north']['x'];
		$targetY = $coords['north']['y'];
		while($tmpX < $targetX){
			try{
				$circuit[] = $this->findByCoords($tmpX, $tmpY, $map);
			}
			catch(InvalidCoordinatesException $e){}
			$tmpX++;
		}

		return $circuit;
	}
}

class InvalidCoordinatesException extends \Exception {}
