<?php
use Nette\Diagnostics\Debugger;

/**
 * A game stat container, which provides access to values which are results of rules interaction
 * @author Jan "Teyras" Buchar
 */
class StatContainer extends Nette\Object
{
	/** @var Nette\DI\Container */
	protected $context;

	/** @var array of Stats\AbstractStat */
	protected $instances;
	
	/**
	 * Constructor
	 * @param Nette\DI\Container
	 */
	public function __construct (Nette\DI\Container $context)
	{
		$this->context = $context;
	}

	/**
	 * Overloaded getter
	 * @param string
	 * @return Stats\AbstractStat
	 */
	public function & __get ($name)
	{
		if (!isset($this->instances[$name])) {
			$class = 'Stats\\' . lcfirst($name);
			$this->instances[$name] = new $class($this->context);
		}
		return $this->instances[$name];
	}
}
