<?php
/**
 * A loader of rules (or game mechanics) object representations
 * @author Jan "Teyras" Buchar
 */
class RuleLoader extends Nette\Object
{
	/** @var Nette\DI\Container */
	protected $context;
	
	/** @var Nette\Loaders\RobotLoader */
	protected $robotLoader;
	
	/** @var Nette\Caching\Cache*/
	protected $cache;
	
	/** @var array */
	protected $aliases;
	
	/** @var array*/
	protected $index;
	
	/** @var array */
	protected $instances;
	
	/**
	 * Constructor
	 * @param Nette\DI\Container
	 * @param Nette\Loaders\RobotLoader
	 * @param Nette\Caching\Storages\IStorage
	 * @param array
	 */
	public function __construct ($context, $robotLoader, $cacheStorage, $aliases)
	{
		$this->context = $context;
		$this->robotLoader = $robotLoader;
		$this->cache = new Nette\Caching\Cache($cacheStorage, 'Rules');
		$this->aliases = $aliases;
		foreach ($aliases as $type) {
			$this->instances[$type] = array();
		}
		$this->restoreIndex();
	}
	
	/**
	 * Get a rule specified by its type and name
	 * @param string
	 * @param string
	 * @return object
	 */
	public function get ($type, $rule)
	{
		if (!isset($this->instances[$type][$rule])) {
			$class = $this->aliases[$type] . '\\' . ucfirst($rule);
			$this->instances[$type][$rule] = new $class($this->context);
		}
		return $this->instances[$type][$rule];
	}
	
	/**
	 * Get all rules of given type
	 * @param string
	 * @return array
	 */
	public function getAll ($type)
	{
		$result = array();
		foreach ($this->index[$type] as $rule) {
			$result[lcfirst($rule)] = $this->get($type, $rule);
		}
		return $result;
	}
	
	/**
	 * Get the descriptions of all the rules
	 * @return array of string
	 */
	public function getDescriptions ()
	{
		$result = array();
		foreach ($this->aliases as $alias => $type) {
			$result[$alias] = array();
			foreach ($this->getAll($alias) as $name => $rule) {
				$result[$alias][$name] = $rule->getDescription();
			}
		}
		return $result;
	}
	
	/**
	 * Load the index of known rules and rebuild it if necessary
	 * @return void
	 */
	protected function restoreIndex ()
	{
		$classes = $this->robotLoader->getIndexedClasses();
		$checksum = md5(serialize($classes));
		if ($this->cache->load('checksum') !== $checksum) {
			$index = array();
			foreach ($classes as $class => $file) {
				if ($type = array_search(substr($class, 0, $pos = strrpos($class, '\\')), $this->aliases)) {
					if (!isset($index[$type])) {
						$index[$type] = array();
					}
					if (!interface_exists($class)) {
						$index[$type][] = substr($class, $pos + 1);
					}
				}
			}
			$this->cache->save('index', $index);
			$this->cache->save('checksum', $checksum);
		} else {
			$index = $this->cache->load('index');
		}
		$this->index = $index;
	}
}