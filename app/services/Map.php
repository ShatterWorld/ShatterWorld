<?php
class Map extends Nette\Object
{
	/** @var array */
	protected $params;
	
	/** @var ModelContainer */
	protected $model;
	
	/** @var Nette\Caching\Cache */
	protected $cache;
	
	/** @var array */
	protected $fieldRanks;
	
	/** @var array */
	protected $center;
	
	/** @var bool */
	protected $open = FALSE;
	
	/**
	 * Constructor
	 * @param array
	 * @param ModelContainer
	 * @param Nette\Caching\IStorage
	 */
	public function __construct ($params, ModelContainer $model, Nette\Caching\IStorage $storage)
	{
		$this->params = $params;
		$this->model = $model;
		$this->cache = new Nette\Caching\Cache($storage, 'Map');
		$this->center = array('x' => intval($params['size'] / 2), 'y' => intval($params['size'] / 2));
	}
	
	/**
	 * Get a reference to a two-dimensional array of field ranks
	 * @return array
	 */
	protected function & getFieldRanks ()
	{
		if (!$this->fieldRanks && !($this->fieldRanks = $this->cache->load('FieldRanks'))) {
			$this->fieldRanks = array($this->center['x'] => array($this->center['y'] => 1));
		}
		return $this->fieldRanks;
	}
	
	/**
	 * Open the map for transactional writing
	 * @return void
	 */
	public function open ()
	{
		$this->open = TRUE;
	}
	
	/**
	 * Close the map and finish the transaction by writing data into the cache
	 * @return void
	 */
	public function close ()
	{
		$this->open = FALSE;
		$this->saveRanks();
	}
	
	/**
	 * Save ranks into the cache
	 * @return void
	 */
	protected function saveRanks ()
	{
		$this->cache->save('FieldRanks', $this->fieldRanks);
	}
	
	/**
	 * Mark a field as occupied, then mark surrounding neutral field as 
	 * unsuitable for occupation and the outermost circle as suitable for occupation
	 * @param int
	 * @param int
	 * @return void
	 */
	public function occupyField ($x, $y)
	{
		$this->setRank($x, $y, 0);
		for ($i = 1; $i <= $this->params['playerDistance']; $i++) {
			foreach ($this->getCircuit($x, $y, $i) as $field) {
				$this->setRank($field['x'], $field['y'], 0);
			}
		}
		foreach ($this->getCircuit($x, $y, $this->params['playerDistance'] + 1) as $field) {
			$this->setRank($field['x'], $field['y'], 1);
		}
		if (!$this->open) {
			$this->saveRanks();
		}
	}
	
	/**
	 * Get the field with the highest rank (closest to the center)
	 * @throws Exception
	 * @return array
	 */
	public function getHighestRankedField ()
	{
		$result = array(
			'x' => NULL,
			'y' => NULL
		);
		$maxRank = 0;
		foreach ($this->getFieldRanks() as $x => $row) {
			foreach ($row as $y => $rank) {
				if ($rank > $maxRank) {
					$maxRank = $rank;
					$result['x'] = $x;
					$result['y'] = $y;
				}
			}
		}
		if ($maxRank > 0) {
			return $result;
		} else {
			throw new Exception;
		}
	}
	
	/**
	 * Set the rank of a field
	 * @param integer
	 * @param integer
	 * @param integer
	 */
	protected function setRank ($x, $y, $rank)
	{
		if ($this->checkCoords($x, $y)) {
			$ranks = & $this->getFieldRanks();
			if (!isset($ranks[$x])) {
				$ranks[$x] = array();
			}
			$distance = $this->calculateDistance($this->center, array('x' => $x, 'y' => $y));
			$rank = $distance > 0 ? $rank / $distance : 0;
			if (!(isset($ranks[$x][$y]) && $ranks[$x][$y] < $rank)) {
				$ranks[$x][$y] = $rank;
			}
		}
	}
	
	/**
	 * Are given coordinates within the area of the map?
	 * @param integer
	 * @param integer
	 * @return bool
	 */
	protected function checkCoords ($x, $y) 
	{
		return $x > 0 && $x < $this->params['size'] && $y > 0 && $y < $this->params['size'];
	}
	
	/**
	 * Calculates distance between field $a and $b
	 * @param array ('x' => integer, 'y' => integer)
	 * @param array ('x' => integer, 'y' => integer)
	 * @return integer
	 */
	public function calculateDistance ($a, $b)
	{
		$sign = function ($x) {
			return $x == 0 ? 0 : (abs($x) / $x);
		};
		$dx = $b['x'] - $a['x'];
		$dy = $b['y'] - $a['y'];

		if ($sign($dx) === $sign($dy)) {
			return abs($dx) + abs($dy);
		} else {
			return max(abs($dx), abs($dy));
		}
	}
	
	/**
	 * Get a circle of field surrounding given field within given radius
	 * @param integer
	 * @param integer
	 * @param integer
	 * @return array
	 */
	public function getCircuit ($x, $y, $r)
	{
		$coords = array(
			'north' => array('x' => $x + $r, 'y' => $y - $r),
			'south' => array('x' => $x - $r, 'y' => $y + $r),
			'north-west' => array('x' => $x, 'y' => $y - $r),
			'north-east' => array('x' => $x + $r, 'y' => $y),
			'south-west' => array('x' => $x - $r, 'y' => $y),
			'south-east' => array('x' => $x, 'y' => $y + $r)
		);

		static $vectors = array(
			array('origin' => 'north', 'target' => 'north-east', 'direction' => array(0, 1)),
			array('origin' => 'north-east', 'target' => 'south-east', 'direction' => array(-1, 1)),
			array('origin' => 'south-east', 'target' => 'south', 'direction' => array(-1, 0)),
			array('origin' => 'south', 'target' => 'south-west', 'direction' => array(0, -1)),
			array('origin' => 'south-west', 'target' => 'north-west', 'direction' => array(1, -1)),
			array('origin' => 'north-west', 'target' => 'north', 'direction' => array(1, 0)),
		);

		$circuit = array();
		
		foreach ($vectors as $vector) {
			$tmpX = $coords[$vector['origin']]['x'];
			$tmpY = $coords[$vector['origin']]['y'];
			$targetX = $coords[$vector['target']]['x'];
			$targetY = $coords[$vector['target']]['y'];
			$dirX = $vector['direction'][0];
			$dirY = $vector['direction'][1];
			while (($dirX === 0 or $dirX * ($targetX - $tmpX) > 0) and
				($dirY === 0 or $dirY * ($targetY - $tmpY) > 0)) {
				if ($this->checkCoords($x, $y)) {
					$circuit[] = array('x' => $tmpX, 'y' => $tmpY);
				}
				$tmpX = $tmpX + $dirX;
				$tmpY = $tmpY + $dirY;
			}
		}

		return $circuit;
	}
}