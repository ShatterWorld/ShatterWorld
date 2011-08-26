<?php
class RuleLoader extends Nette\Object
{
	/** @var array */
	protected $aliases;
	
	/** @var array */
	protected $instances;
	
	/**
	 * Constructor
	 * @param array
	 */
	public function __construct ($aliases)
	{
		$this->aliases = $aliases;
		foreach ($aliases as $type) {
			$this->instances[$type] = array();
		}
	}
	
	public function getRule ($type, $object)
	{
		if (!isset($this->instances[$type][$object])) {
			$class = $this->aliases[$type] . '\\' . lcfirst($object);
			$this->instances[$type][$object] = new $class;
		}
		return $this->instances[$type][$object];
	}
}