<?php
use Nette\Diagnostics\Debugger;

/**
 * Graph class
 * @author Petr Bělohlávek
 */
class Graph
{

	const SHORT = 0;
	const CHEAP = 1;

	/**
	 * The edges of graph
	 * @var array of array of int
	 */
	protected $edges;

	/**
	 * The vertices of graph
	 * @var utils\ArraySet
	 */
	protected $vertices;

	/**
	 * Is floydRes updated?
	 * @var boolean
	 */
	protected $pathUpdated;

	/**
	 * Constructor
	 * @return Graph
	 */
	public function __construct ()
	{
		$this->edges = array();
		$this->vertices = new ArraySet();
		$this->pathUpdated = false;
	}

	/**
	 * Adds the vertice
	 * @param int
	 * @param int
	 * @return boolean
	 */
	public function addVertice ($id, $value)
	{
		if ($this->vertices->addElement($id, $value)){
			$this->pathUpdated = false;
			return true;
		}
		return false;
	}

	/**
	 * Deletes the vertice
	 * @param int
	 * @return boolean
	 */
	public function removeVertice ($id)
	{
		if ($this->vertices->deleteElement($id)){
			$this->pathUpdated = false;
			return true;
		}
		return false;
	}

	/**
	 * Updates the vertice
	 * @param int
	 * @param int
	 * @return boolean
	 */
	public function updateVertice ($id, $newValue)
	{
		if ($this->vertices->updateElement($id, $newValue)){
			$this->pathUpdated = false;
			return true;
		}
		return false;
	}

	/**
	 * Returns the vertices ids
	 * @return utils\ArraySet
	 */
	public function getVerticesIds ()
	{
		return array_keys($this->vertices->toArray());
	}

	/**
	 * Returns ids of the vertices
	 * @return array of int
	 */
	public function getVertices ()
	{
		return $this->vertices;
	}

	/**
	 * Adds or update the edge
	 * @throws Exception
	 * @param int
	 * @param int
	 * @param float
	 * @return void
	 */
	public function addEdge ($from, $to, $value = 0)
	{
		if (!$this->vertices->offsetExists($from) || !$this->vertices->offsetExists($to)){
			throw new Exception('Can not add the edge from/to vertice which doesnt exist ('.$from.'/'.$to.')');
		}
		$this->edges[$from][$to] = $value;
		$this->pathUpdated = false;
	}

	/**
	 * Remove the edge
	 * @param int
	 * @param int
	 * @return void
	 */
	public function removeEdge ($from, $to)
	{
		unset($this->edges[$from][$to]);
		$this->pathUpdated = false;
	}

	/**
	 * Returns the edges of graph
	 * @return array of array of in
	 */
	public function getEdges ()
	{
		return $this->edges;
	}


	/**
	 * Returns and unset the smallest element
	 * @param *array of int
	 * @return int
	 */
	protected function popSmallest(&$arr){

		//Debugger::barDump($arr);
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
	protected function getNeighbours ($from){
		//Debugger::barDump($from);
		$neighbours = array();
		foreach($this->edges[$from] as $key => $value){
			$neighbours[$key] = $value;
		}
		return $neighbours;
	}

	/**
	 * Finds shortest paths from given vertice to all other vertices
	 * @param int
	 * @return array of int
	 */
	protected function dijkstraEdges ($from)
	{
		//init
		$lengths = array();
		$prevVertices = array();
		$vertices = $this->getVerticesIds();
		$prevVertices[$from] = null;
		$lengths[$from] = 0;

		// alg
		while(count($vertices) > 0){
			$u = $this->popSmallest($vertices);//id
			$neighbours = $this->getNeighbours($u);

			//Debugger::barDump($neighbours);
			foreach($neighbours as $key => $neighbour){
				if (isset($lengths[$u])){
					$potentialLength = $lengths[$u] + $neighbour;
					if(!isset($lengths[$key]) || $potentialLength < $lengths[$key]){
						$lengths[$key] = $potentialLength;
						$prevVertices[$key] = $u;
					}
				}
			}
		}

		return $prevVertices;
	}

	/**
	 * Finds cheapest paths from given vertice to all other vertices
	 * @param int
	 * @return array of int
	 */
	protected function dijkstraVertices ($from)
	{
		//init
		$lengths = array();
		$prevVertices = array();
		$vertices = $this->getVerticesIds();
		$prevVertices[$from] = null;
		$lengths[$from] = 0;

		// alg
		while(count($vertices) > 0){
			$u = $this->popSmallest($vertices);//id
			$neighbours = $this->getNeighbours($u);

			foreach($neighbours as $key => $neighbour){
				//$ver = $this->vertices->offsetGet($key);//price of neigh vertice
				if (isset($lengths[$u])){
					$potentialLength = $lengths[$u] + $neighbour;
					if(!isset($lengths[$key]) || $potentialLength < $lengths[$key]){
						$lengths[$key] = $potentialLength;
						$prevVertices[$key] = $u;
					}
				}
			}
		}

		return $prevVertices;
	}

	/**
	 * Finds shortest path from/to vertices given
	 * @param int
	 * @param int
	 * @param int
	 * @return array of vertrices
	 */
	public function getPath ($pathType, $from, $to)
	{
		/* needs prev. routes saving (by $from)
		 * +even caching the whole graph*/
		if ($pathType === self::SHORT){
			$routes = $this->dijkstraEdges($from);
		}
		else if ($pathType === self::CHEAP){
			$routes = $this->dijkstraVertices($from);
		}
		else{
			throw new Exception('No such path type: '.$pathType);
		}

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







