<?php
namespace Stats;
use Nette;

class AbstractStat 
{
	/** @var Nette\DI\Container */
	private $context;
	
	/**
	 * Constructor
	 * @param Nette\DI\Container
	 */
	public function __construct (Nette\DI\Container $context)
	{
		$this->context = $context;
	}
	
	/**
	 * Context getter
	 * @return Nette\DI\Container
	 */
	public function getContext ()
	{
		return $this->context;
	}
}