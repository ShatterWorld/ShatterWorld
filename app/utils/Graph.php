<?php
use Nette\Diagnostics\Debugger;

/**
 * Graph class
 * @author Petr Bělohlávek
 */
class Graph
{

	/**
	 * Data of graph
	 * @var array of array of int
	 */
	protected $data;

	/**
	 * Result of floyd-warshall
	 * @var array of array of int
	 */
	protected $floydRes;

	/**
	 * The shortest path
	 * @var array of int
	 */
	protected $path;

	/**
	 * Is floydRes updated?
	 * @var boolean
	 */
	protected $pathUpdated;

	/**
	 * Vertices
	 * @var ArraySet of int
	 */
	protected $vertices;

	/**
	 * Max edge value
	 * @var int
	 */
	protected $maxValue;

	/**
	 * Max edge value
	 * @var int
	 */
	protected $maxKey;


	/**
	 * Constructor
	 * @return Graph
	 */
	public function __construct ()
	{
		$this->vertices = new ArraySet();
		$this->data = array();
		$this->floydRes = array();
		$this->pathUpdated = false;
		$this->path = array();
		$this->maxValue = -1;
		$this->maxVertice = -1;
	}

	/**
	 * Returns the data of graph
	 * @return array of array of in
	 */
	public function getGraph ()
	{
		return $this->data;
	}

	/**
	 * Adds or update the edge
	 * @param int
	 * @param int
	 * @param float
	 * @return void
	 */
	public function addEdge ($from, $to, $value = 0)
	{
		$this->data[$from][$to] = $value;
		$this->vertices->addElement($from, 0);
		$this->vertices->addElement($to, 0);
		$this->pathUpdated = false;

		if($from > $this->maxVertice){
			$this->maxVertice = $from;
		}
		if($to > $this->maxVertice){
			$this->maxVertice = $to;
		}
	}

	/**
	 * Removed the edge
	 * @param int
	 * @param int
	 * @return void
	 */
	public function removeEdge ($from, $to)
	{
		unset($this->data[$from][$to]);
		$this->pathUpdated = false;
	}

	/**
	 * Update the vertice
	 * @param int
	 * @param float
	 * @return void
	 */
	public function updateVertice ($id, $value)
	{
		$this->vertices->updateElement($id, $value);
		$this->pathUpdated = false;
	}


	protected function floydWarshall ()
	{
		$vertices = array_keys($this->data);

		for($i = 0; $i <= $this->maxVertice; $i++){
			for($j = 0; $j <= $this->maxVertice; $j++){
				if (!isset($this->data[$i][$j])){
					$this->data[$i][$j] =  &$this->maxValue;
				}
			}
		}
		//Debugger::barDump($vertices);
		//Debugger::barDump($this->maxVertice);

/*		for ($k = 0; $k <= $this->maxVertice; $k++){
			for ($i = 0; $i <= $this->maxVertice; $i++){
				for ($j = 0; $j <= $this->maxVertice; $j++){
					* */
		foreach ($vertices as $k){
			foreach ($vertices as $i){
				foreach ($vertices as $j){
					if ($this->data[$i][$k] + $this->data[$k][$j] < $this->data[$i][$j]){
						$this->data[$i][$j] = $this->data[$i][$k] + $this->data[$k][$j];
						$this->floydRes[$i][$j]=$k;
					}
				}
			}
		}

		$this->pathUpdated = true;
	}

	protected function findPath ($from, $to)
	{
		if (isset($this->floydRes[$from][$to])){
			$k = $this->floydRes[$from][$to];
			if ($k != 0){
				$this->findPath($from, $k);
				$this->path[] = $k;
				$this->findPath($k, $to);
			}
		}
	}

	public function getPath ($from, $to)
	{
		$this->path = array();
		//Debugger::barDump($this->pathUpdated);

		if(!$this->pathUpdated){
			$this->floydWarshall();
		}

		//Debugger::barDump($this->pathUpdated);

		$this->findPath($from, $to);
		Debugger::barDump($this->path);
		return $this->path;
	}

}







