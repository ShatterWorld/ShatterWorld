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
	 * Get fields under given coordinates
	 * @param array of arrays containing coordinates
	 * @param array
	 * @return array
	 */
	protected function getCoordinateValues ($coordinates, $map = array())
	{
		if ($map) {
			return array_map(function ($coord) use ($map) {
				return $map[$coord['x']][$coord['y']];
			}, $coordinates);
		} else {
			$qb = $this->createQueryBuilder('f');
			$qb->where(call_user_func_array(callback($qb->expr(), 'orX'), array_map(function ($coord) use ($qb) {
				return $qb->expr()->andX(
					$qb->expr()->eq('f.coordX', $coord['x']),
					$qb->expr()->eq('f.coordY', $coord['y'])
				);
			}, $coordinates)));
			return $qb->getQuery()->getResult();
		}

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

		for ($d = 1; $d <= $depth; $d++) {
			$neighbours = array_merge($neighbours, $this->context->map->getCircuit($field->x, $field->y, $d));
		}

		return $this->getCoordinateValues($neighbours, $map);
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
		$result = $this->getCoordinateValues(array(array('x' => $x, 'y' => $y)), $map);
		return array_pop($result);
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
				$depths->addElement($field->owner->id, $this->context->stats->visibility->getRadius($field->owner));
			}
			foreach ($this->getFieldNeighbours($field, $depths->offsetGet($field->owner->id), $map) as $neighbour) {
				$id = intval($neighbour);
				$visibleFields->addElement($id, $id);
			}

		}
		return array_values($visibleFields->toArray());
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
		return $this->fetchFieldsData($clan, $this->getVisibleFieldsIds($clan));
	}

	public function getMapArray ()
	{
		return $this->fetchFieldsData();
	}
	
	protected function fetchFieldsData (Entities\Clan $clan = NULL, $ids = array())
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('f', 'o', 'a', 'c')->from('Entities\Field', 'f')->leftJoin('f.owner', 'o')->leftJoin('o.alliance', 'a')->leftJoin('f.facility', 'c');
		if ($ids) {
			$qb->where($qb->expr()->in('f.id', $ids));
		}
		$unitsQb = $this->getEntityManager()->createQueryBuilder();
		$unitsQb->select('u', 'l')->from('Entities\Unit', 'u')->innerJoin('u.location', 'l');
		$conds = array();
		if ($clan) {
			$conds[] = $unitsQb->expr()->eq('u.owner', $clan->id);
		}
		if ($ids) {
			$conds[] = $unitsQb->expr()->in('u.location', $ids);
		}
		if ($conds) {
			$unitsQb->where(call_user_func_array(callback($unitsQb->expr(), 'andX'), $conds));
		}
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
			if ($clan && ($row['owner'] != null && $row['owner']['id'] != $clan->id)) {
				$row['facility'] = null;
				$row['level'] = null;
			}
			$row['units'] = isset($units[$row['id']]) ? $units[$row['id']] : null;
			$result[$x][$y] = $row;
		}
		return $result;
	}

	/**
	 * Determines whether the field given is neighbour of clan given
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return bool
	 */
	public function isNeighbourOf (Entities\Field $field, Entities\Clan $clan = null)
	{
		$neighbours = $this->getFieldNeighbours($field);
		foreach ($neighbours as $neighbour) {
			if ($neighbour->owner === $clan) {
				return true;
			}
		}
		return false;
	}

}


