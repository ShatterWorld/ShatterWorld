<?php
namespace Repositories;
use Nette;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;
use Nette\Caching\Cache;


class Field extends BaseRepository {

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

	public function getIndexedMapIds ()
	{
		$result = array();
		foreach ($this->createQueryBuilder('f')->select('f.id', 'f.coordX', 'f.coordY')->getQuery()->getArrayResult() as $row) {
			$x = $row['coordX'];
			$y = $row['coordY'];
			if (!isset($result[$x])) {
				$result[$x] = array();
			}
			$result[$x][$y] = $row['id'];
		}
		return $result;
	}
	
	public function findIdByCoords ($x, $y, &$map = array())
	{
		$mapSize = $this->context->params['game']['map']['size'];
		if ($x >= $mapSize || $y >= $mapSize || $x < 0 || $y < 0) {
			throw new InvalidCoordinatesException;
		}
		if (count($map) > 0){
			return $map[$x][$y];
		}

		$qb = $this->createQueryBuilder('f');
		$qb->select('f.id')->where($qb->expr()->andX(
			$qb->expr()->eq('f.coordX', $x),
			$qb->expr()->eq('f.coordY', $y)
		));

		return $qb->getQuery()->getSingleScalarResult();
	}
	
	/**
	 * Get the count of fields owned by given clan
	 * @param Entities\Clan
	 * @return int
	 */
	public function getTerritorySize (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select($qb->expr()->count('f.id'))
			->from($this->getEntityName(), 'f')
			->where($qb->expr()->eq('f.owner', $clan->id));
		return $qb->getQuery()->getSingleScalarResult();
	}

	public function getClanFacilities (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('max(f.level) level', 'f.facility')
			->from($this->getEntityName(), 'f')
			->where($qb->expr()->andX(
				$qb->expr()->isNotNull('f.facility'),
				$qb->expr()->eq('f.owner', $clan->id)))
			->groupBy('f.facility');
		$result = array();
		foreach ($qb->getQuery()->getArrayResult() as $row) {
			$result[$row['facility']] = $row['level'];
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
	public function getVisibleFields ($clanId, $depth)
	{

		$qb = $this->createQueryBuilder('f');
		$qb->where(
			$qb->expr()->eq('f.owner', $clanId)
		);
		$ownerFields = $qb->getQuery()->getResult();
		$map = $this->getIndexedMapIds();
		$visibleFields = array();
		foreach($ownerFields as $ownerField){
			$neighbours = $this->getFieldNeighbours($ownerField, $depth, $map);

			foreach($neighbours as $neighbour){
				if(array_search($neighbour, $visibleFields, true) === false){
// 					if ($neighbour->owner != null && $neighbour->owner->id != $clanId){
// 						$neighbour->facility = null;
// 						$neighbour->level = null;
// 					}
					$visibleFields[] = $neighbour;
				}
			}
		}

		return $visibleFields;
	}

	public function getVisibleFieldsArray ($clanId, $depth)
	{
// 		$result = array();
// 		foreach ($this->getVisibleFields($clanId, $depth) as $field) {
// 			$result[] = $field->toArray();
// 		}
		$cache = new Cache($this->context->cacheStorage, 'VisibleFields');
		$ids = $cache->load($clanId);
		if ($ids === NULL) {
			$cache->save($clanId, $ids = $this->getVisibleFields($clanId, $depth));
		}
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('f', 'o', 'a')->from('Entities\Field', 'f')->leftJoin('f.owner', 'o')->leftJoin('o.alliance', 'a');
		$qb->where($qb->expr()->in('f.id', $ids));
		return $qb->getQuery()->getArrayResult();
	}

	/**
	 * Finds centers of seven-fields-sized hexagons which are located $outline from the center of the map. The hexagons are chosen only if $playerDistance fields around are neutral. $S is the center of the map.
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
		if ($r === 1) {
			return $this->getFieldNeighbours($S, 1, $map);
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

		$vectors = array(
			array('origin' => 'north', 'target' => 'north-east', 'direction' => array(0, 1)),
			array('origin' => 'north-east', 'target' => 'south-east', 'direction' => array(-1, 1)),
			array('origin' => 'south-east', 'target' => 'south', 'direction' => array(-1, 0)),
			array('origin' => 'south', 'target' => 'south-west', 'direction' => array(0, -1)),
			array('origin' => 'south-west', 'target' => 'north-west', 'direction' => array(1, -1)),
			array('origin' => 'north-west', 'target' => 'north', 'direction' => array(1, 0)),
		);
		
		foreach ($vectors as $vector) {
			$tmpX = $coords[$vector['origin']]['x'];
			$tmpY = $coords[$vector['origin']]['y'];
			$targetX = $coords[$vector['target']]['x'];
			$targetY = $coords[$vector['target']]['y'];
			$dirX = $vector['direction'][0];
			$dirY = $vector['direction'][1];
			while (($dirX === 0 or $dirX * ($targetX - $tmpX) > 0) and 
				($dirY === 0 or $dirY * ($targetY - $tmpY) > 0)) {
				try {
					$circuit[] = $this->findIdByCoords($tmpX, $tmpY, $map);
				}
				catch (InvalidCoordinatesException $e) {}
				$tmpX = $tmpX + $dirX;
				$tmpY = $tmpY + $dirY;
			}
		}

		return $circuit;
	}
}

class InvalidCoordinatesException extends \Exception {}
