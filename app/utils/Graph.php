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

	/**
	 * Returns and unset the smallest element
	 * @param *array of int
	 * @return int
	 */
	protected function popSmallest(&$arr){
		$value = null;
		$key = null;
		foreach($arr as $arrKey => $item){
			if($value === null || $item < $value){
				$value = $item;
				$key = $arrKey;
			}
		}
		unset($arr[$key]);
		return $value;
	}

	/**
	 * Returns the array of ids of all vertices which can be accessed from vertice id given
	 * @param int
	 * @return array of int
	 */
	protected function getNeighbours($from){
		$neighbours = array();
		foreach($this->data[$from] as $key => $value){
			$neighbours[$key] = $value;
		}
		return $neighbours;
	}

	/**
	 * Finds all shortest path from vertice given to all other vertices, -1 if the path doesnt exist
	 * @param int
	 * @return array of int
	 */
	protected function dijkstra ($from)
	{
		//init
		$lengths = array();
		$prevVertices = array();
		$vertices = array_keys($this->data);
		foreach ($vertices as $key => $vertice){
			$lengths[$vertice] = 100000;
			$prevVertices[$vertice] = null;
		}
		$lengths[$from] = 0;

		// alg
		while(count($vertices) > 0){
			$u = $this->popSmallest($vertices);//id
			$neighbours = $this->getNeighbours($u);

			foreach($neighbours as $key => $neighbour){
				$potentialLength = $lengths[$u] + $neighbour;
				if($potentialLength < $lengths[$key]){
					$lengths[$key] = $potentialLength;
					$prevVertices[$key] = $u;
				}
			}
		}

		return $prevVertices;
	}

	/**
	 * Finds shortest path from/to vertices given
	 * @param int
	 * @param int
	 * @return array of vertrices
	 */
	public function getPath ($from, $to)
	{
		/*needs prev. routes saving (by $from)/even caching?*/
		$routes = $this->dijkstra($from);

		$path = array();
		$tmp = $routes[$to];
		while($tmp !== null){
			array_unshift($path, $tmp);
			$tmp = $routes[$tmp];
		}
		array_shift($path);

		return $path;
	}

}







