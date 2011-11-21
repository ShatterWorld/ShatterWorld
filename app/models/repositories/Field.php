<?php
namespace Repositories;
use Nette;
use Doctrine;
use Entities;
use Nette\Diagnostics\Debugger;
use Nette\Caching\Cache;
use ArraySet;

class Field extends BaseRepository
{
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
	 * Returns map of field ids indexed by coordinates
	 * @return array of arrays of integers
	 */
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

	/**
	 * Field by [x;y] coords
	 * @param int
	 * @param int
	 * @param array of int
	 * @return Entities\Field
	 */
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

	/**
	 * Get the clan facilities
	 * @param Entities\Clan
	 * @return array of Entities\Facility
	 */
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
	 * Get the clan available facility changes
	 * @param Entities\Clan
	 * @return
	 */
	public function getAvailableFacilityChanges (Entities\Clan $clan)
	{
		$qb = $this->createQueryBuilder('f');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('f.owner', $clan->id),
			$qb->expr()->isNotNull('f.facility')
		));
		$qb->groupBy('f.facility, f.level');
		$result = array(
			'upgrades' => array(),
			'downgrades' => array(),
			'demolitions' => array()
		);
		foreach ($qb->getQuery()->getResult() as $field) {
			$rule = $this->context->rules->get('facility', $field->facility);
			if ($field->level < $this->context->params['game']['stats']['facilityLevelCap']) {
				if (!isset($result['upgrades'][$field->facility])) {
					$result['upgrades'][$field->facility] = array();
				}
				$level = $field->level + 1;
				$info = array();
				$info['cost'] = $rule->getConstructionCost($level);
				$info['time'] = $rule->getConstructionTime($level);
				$result['upgrades'][$field->facility][$level] = $info;
			}
			if ($field->level > 1) {
				if (!isset($result['downgrades'][$field->facility])) {
					$result['downgrades'][$field->facility] = array();
				}
				$level = $field->level - 1;
				$info = array();
				$info['cost'] = $rule->getDemolitionCost($field->level, $level);
				$info['time'] = $rule->getDemolitionTime($field->level, $level);
				$result['downgrades'][$field->facility][$level] = $info;
			}
			if (!isset($result['demolitions'][$field->facility])) {
				$result['demolitions'][$field->facility] = array();
			}
			$info = array();
			$info['cost'] = $rule->getDemolitionCost($field->level, 0);
			$info['time'] = $rule->getDemolitionTime($field->level, 0);
			$result['demolitions'][$field->facility][$field->level] = $info;
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

		} else {
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
	 * Finds fields which are visible for the player from the cache
	 * @return Cache
	 */
	public function getVisibleFieldsCache ()
	{
		return new Nette\Caching\Cache($this->context->cacheStorage, 'VisibleFields');
	}

	/**
	 * Finds fields which are visible for the player
	 * @param Entities\Clan
	 * @return ArraySet of Entities\Field
	 */
	public function getVisibleFields (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('f', 'o', 'a')->from('Entities\Field', 'f')->innerJoin('f.owner', 'o')->leftJoin('o.alliance', 'a');
		$qb->where($qb->expr()->in('f.id', $this->getVisibleFieldsIds($clan)));
		return $qb->getQuery()->getResult();
	}

	/**
	 * Finds fields which are visible for the player
	 * @param Entities\Clan
	 * @return ArraySet of Entities\Field
	 */
	protected function computeVisibleFields (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('f')->from('Entities\Field', 'f')->innerJoin('f.owner', 'o');
		if ($clan->alliance !== null) {
			$qb->where(
				$qb->expr()->orX(
					$qb->expr()->eq('o.id', $clan->id),
					$qb->expr()->eq('o.alliance', $clan->alliance->id)
				)
			);
		} else {
			$qb->where($qb->expr()->eq('o.id', $clan->id));
		}

		$baseFields = $qb->getQuery()->getResult();
		$map = $this->getIndexedMapIds();

		$visibleFields = new ArraySet();
		$depths = new ArraySet();
		foreach ($baseFields as $field) {
			if (!$depths->offsetExists($field->owner->id)){
				$depths->addElement($field->owner->id, $this->context->stats->getVisibilityRadius($field->owner));
			}
			foreach ($this->getFieldNeighbours($field, $depths->offsetGet($field->owner->id), $map) as $neighbour) {
				$id = intval($neighbour);
				$visibleFields->addElement($id, $id);
			}

		}
		return $visibleFields->toArray();
	}

	/**
	 * Finds visible fields' id's
	 * @param Entities\Clan
	 * @return array of integers
	 */
	public function getVisibleFieldsIds (Entities\Clan $clan)
	{
		$cache = $this->getVisibleFieldsCache();
		$ids = $cache->load($clan->id);
		if ($ids === NULL) {
			$ids = $this->computeVisibleFields($clan);
			$cache->save($clan->id, $ids);
		}
		return $ids;
	}

	/**
	 * Finds visible fields and returns them as the 2d array of integers (their coords)
	 * @param Entities\Clan
	 * @return 2d array of integers
	 */
	public function getVisibleFieldsArray (Entities\Clan $clan)
	{
		$ids = $this->getVisibleFieldsIds($clan);
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('f', 'o', 'a')->from('Entities\Field', 'f')->leftJoin('f.owner', 'o')->leftJoin('o.alliance', 'a')
			->where($qb->expr()->in('f.id', $ids));
		$unitsQb = $this->getEntityManager()->createQueryBuilder();
		$unitsQb->select('u', 'l')->from('Entities\Unit', 'u')->innerJoin('u.location', 'l')
			->where($unitsQb->expr()->andX(
				$unitsQb->expr()->in('u.location', $ids),
				$unitsQb->expr()->eq('u.owner', $clan->id)
			));
		$units = array();
		foreach ($unitsQb->getQuery()->getArrayResult() as $row) {
			if (!isset($units[$row['location']['id']])) {
				$units[$row['location']['id']] = array();
			}
			$units[$row['location']['id']][$row['type']] = $row;
		}
		$result = array();
		foreach ($qb->getQuery()->getArrayResult() as $row) {
			$x = $row['coordX'];
			$y = $row['coordY'];
			if (!isset($result[$x])) {
				$result[$x] = array();
			}
			if ($row['owner'] != null && $row['owner']['id'] != $clan->id){
				$row['facility'] = null;
				$row['level'] = null;
			}
			$row['units'] = isset($units[$row['id']]) ? $units[$row['id']] : null;
			$result[$x][$y] = $row;
		}
		return $result;
	}

	/**
	 * Finds centers of seven-fields-sized hexagons which are located $outline from the $S.
	 * The hexagons are chosen only if $playerDistance fields around are neutral.
	 * @param Entities\Field
	 * @param int
	 * @param array of Entities\Field
	 * @return array of Entities\Field
	 */
	public function findNeutralHexagons($S, $playerDistance, &$map = null)
	{
		$foundCenters = array();
		$circuit = $this->findCircuit($S, $playerDistance+3, $map);

		foreach($circuit as $field){
			if($field->owner == null){
				$neighbours = $this->getFieldNeighbours($field, $playerDistance+1, $map);
				$add = true;

				foreach($neighbours as $neighbour){
					if($neighbour->owner !== null){
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

	/**
	 * Sorts the given array by the distance from the field given
	 * @param array of Entities\Field
	 * @param Entities\Field
	 * @return void
	 */
	public function sortByDistance (&$fields, $S){
		$fieldRepository = $this;
		usort($fields, function ($a, $b) use ($fieldRepository, $S){
			$dA = $fieldRepository->calculateDistance($S, $a);
			$dB = $fieldRepository->calculateDistance($S, $b);
			if ($dA == $dB) return 0;
			return ($dA < $dB) ? -1 : 1;
		});
	}

}

class InvalidCoordinatesException extends \Exception {}
