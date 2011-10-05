<?php
namespace Rules;
use Nette;

/**
 * A game mechanic representation
 * @author Jan "Teyras" Buchar
 */
interface IRule {
	/**
	 * Constructor
	 * @param Nette\DI\Container
	 */
	public function __construct (Nette\DI\Container $context);
	
	/**
	 * Get the descriptionf of the rule
	 * @return string
	 */
	public function getDescription ();
}