<?php
use ArraySet;

/**
 * Graph class
 * @author Petr Bělohlávek
 */
class Grpah
{

	/**
	 * Data of graph
	 * @return array of array of int
	 */
	protected $data;

	/**
	 * Vertices
	 * @return ArraySet of int
	 */
	protected $vertices;

	/**
	 * Constructor
	 * @return Graph
	 */
	public function __construct ()
	{
		$this->vertices = new ArraySet();
		$this->data = $data;
	}

	/**
	 * Adds or update the edge
	 * @param int
	 * @param int
	 * @param float
	 * @return void
	 */
	public function addEdge ($from, $to, $value = -1)
	{
		$this->data[$from][$to] = $value;
		$this-vertices->addElement($from, -1);
		$this-vertices->addElement($to, -1);
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
	}

	/**
	 * Update the vertice
	 * @param int
	 * @param float
	 * @return void
	 */
	public function updateVertice ($id, $value)
	{
		$this-vertices->updateElement($id, $value);
	}




}







