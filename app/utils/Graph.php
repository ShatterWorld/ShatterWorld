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
	 * Finds shortest paths from given vertice to all other vertices
	 * @param int
	 * @return array of int
	 */
	protected function dijkstraEdges ($from)
	{
		//init
		$lengths = array(); // vertexId => length
		$prevVertices = array();  // i => vertexId
		$vertices = $this->getVerticesIds(); // i => vertexId

		$prevVertices[$from] = null;
		$lengths[$from] = 0;

		// alg
		while(count($vertices) > 0){ // while some vertices remain
			$minValue = null;
			$minKey = null;
			foreach($vertices as $i => $vertexKey){ //pop the one with shortest path
				if (isset($lengths[$vertexKey])){
					if($minValue === null || $lengths[$vertexKey] < $minValue){
						$minValue = $lengths[$vertexKey];
						$minKey = $i;
					}
				}
			}
			if($minValue === null || $minKey === null){ //or pop random
				reset($vertices);
				list($minKey, $minValue) = each($vertices);
			}
			$u = $vertices[$minKey];
			unset($vertices[$minKey]);

			if (isset($lengths[$u])){
				$neighbours = array();
				foreach($this->edges[$u] as $key => $distance){ // get distances of neighbours
					$neighbours[$key] = $distance;
				}
				foreach($neighbours as $neighbourId => $neighbourDistance){
					$potentialLength = $lengths[$u] + $neighbourDistance;
					if(!isset($lengths[$neighbourId]) || $potentialLength < $lengths[$neighbourId]){ // check better route
						$lengths[$neighbourId] = $potentialLength;
						$prevVertices[$neighbourId] = $u;
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
		$profitSum = array(); // vertexId => length
		$prevVertices = array();  // i => vertexId
		$vertices = $this->getVerticesIds(); // i => vertexId
		$verticesProfit = $this->vertices->toArray(); // vertexId => profit

		$prevVertices[$from] = null;
		$profitSum[$from] = 0;

		// alg
		while(count($vertices) > 0){ // while some vertices remain
			$minValue = null;
			$minKey = null;
			foreach($vertices as $i => $vertexKey){ // pop the one with lowest profit
				if (isset($profitSum[$vertexKey])){
					if($minValue === null || $profitSum[$vertexKey] < $minValue){
						$minValue = $profitSum[$vertexKey];
						$minKey = $i;
					}
				}
			}
			if($minValue === null || $minKey === null){ //or pop random
				reset($vertices);
				list($minKey, $minValue) = each($vertices);
			}
			$u = $vertices[$minKey];
			unset($vertices[$minKey]);

			if (isset($profitSum[$u])){
				$neighbours = array();
				foreach($this->edges[$u] as $key => $distance){ // get profits of neighbours
					$profit = $verticesProfit[$key];
					$neighbours[$key] = $profit == 0 ? 0.01 : 1000-log($profit);
				}
				foreach($neighbours as $neighbourId => $neighbourDistance){
					$potentialLength = $profitSum[$u] + $neighbourDistance;
					if(!isset($profitSum[$neighbourId]) || $potentialLength < $profitSum[$neighbourId]){ // check better route
						$profitSum[$neighbourId] = $potentialLength;
						$prevVertices[$neighbourId] = $u;
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
		/* needs prev. routes saving (by $from)*/
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







