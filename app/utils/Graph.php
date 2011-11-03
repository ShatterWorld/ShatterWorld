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

	/**
	 * Get the value of vertrice if isset, -1 otherwise
	 * @param int
	 * @param int
	 * @return int
	 */
	protected function getDijkstraData($i, $j){
		if (isset($this->data[$i][$j])){
			return $this->data[$i][$j];
		}
		return -1;

	}

	/**
	 * Finds all shortest path from vertice given to all other vertices, -1 if the path doesnt exist
	 * @param int
	 * @return array of int
	 */
	protected function dijkstra ($from)
	{
		/*memorize the path*/
		$N = 414; //hard madafaka
		$lengths = array();
		$def = array();

//Debugger::barDump($N);
//Debugger::barDump($from);
//Debugger::barDump($this->getDijkstraData($from, 412));

		for ($i = 0; $i <= $N; $i++){
			$def[$i] = false;
			$lengths[$i] = -1;
		}

//Debugger::barDump($lengths);
		$def[$from] = true;
		$lengths[$from] = 0;

		$c = 0;
		do{
			//$c++;
			$w = 0;

			for ($i = 0; $i <= $N; $i++){
				if ((!$def[$i]) && (($w == 0) || ($lengths[$i] < $lengths[$w]))){
					$w = $i;
					//Debugger::barDump($w);
				}
			}
Debugger::barDump($w);

			if ($w != 0){
				$def[$w] = true;
				//Debugger::barDump($def[$w]);
				for ($i = 0; $i <= $N; $i++){
					//Debugger::barDump($this->getDijkstraData($w, $i));
					if (($this->getDijkstraData($w, $i) != -1) && ($lengths[$w] + $this->getDijkstraData($w, $i) < $lengths[$i])){
						$lengths[$i] = $lengths[$w] + $this->getDijkstraData($w, $i);
						Debugger::barDump($lengths[$i]);
					}
				}
			}

		}while($w != 0);

//Debugger::barDump($lengths);
//Debugger::barDump($def);

		return $lengths;
	}

	/**
	 * Finds shortest path from/to vertices given
	 * @param int
	 * @param int
	 * @return array of vertrices
	 */
	public function getPath ($from, $to)
	{
		/*needs cond.*/
		$res = $this->dijkstra($from);
		return $res;
		/*return clans, not dijkstra output*/
	}

}







