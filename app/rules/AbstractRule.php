<?php
namespace Rules;
use Nette;

abstract class AbstractRule extends Nette\Object implements IRule
{
	private $context;
	
	public function __construct (Nette\DI\Container $context)
	{
		$this->context = $context;
	}
	
	final protected function getContext ()
	{
		return $this->context;
	}
}