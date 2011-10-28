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
		foreach($vertices as $k){
			foreach($vertices as $i){
				foreach($vertices as $j){
					if(isset($this->data[$i][$k]) && isset($this->data[$k][$j])){
						if(!isset($this->data[$i][$j])){
							$this->data[$i][$j] = $this->data[$i][$k] + $this->data[$k][$j];
							$this->floydRes[$i][$j]=$k;

						}
						else{
							if($this->data[$i][$k] + $this->data[$k][$j] < $this->data[$i][$j]){
								$this->data[$i][$j] = $this->data[$i][$k] + $this->data[$k][$j];
								$this->floydRes[$i][$j]=$k;
							}
						}


					}
/*					if($this->data[$i][$k] + $this->data[$k][$j] < $this->data[$i][$j]){
						$this->data[$i][$j] = $this->data[$i][$k] + $this->data[$k][$j];
						$this->floydRes[$i][$j]=$k;
					}*/
				}
			}
		}
		$this->pathUpdated = true;
	}

	protected function findPath ($from, $to)
	{
		Debugger::barDump($this->floydRes);
		if (isset($this->floydRes[$from][$to])){
			$k = $this->floydRes[$from][$to];
			$this->findPath($from,$k);
			$this->path[] = $k;
			Debugger::barDump($this->path);
			$this->findPath($k,$to);
		}
	}

	public function getPath ($from, $to)
	{
		//Debugger::barDump($this->pathUpdated);
		if(!$this->pathUpdated) $this->floydWarshall();
		//Debugger::barDump($this->pathUpdated);
		$this->findPath($from, $to);
		Debugger::barDump($this->path);
		return $this->path;
	}

}







