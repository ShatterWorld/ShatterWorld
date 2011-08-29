<?php
namespace Rules;
use Nette;

/**
 * A game mechanic representation
 * @author Jan "Teyras" Buchar
 */
interface IRule {
	public function __construct (Nette\DI\Container $context);
}