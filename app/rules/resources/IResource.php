<?php
namespace Rules\Resources;
use Rules\IRule;

interface IResource extends IRule
{
	/**
	 * Value
	 * @return float
	 */
	public function getValue ();
}
