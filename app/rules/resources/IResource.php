<?php
namespace Rules\Resources;
use Rules\IRule;

interface IResource extends IRule
{
	/**
	 * Get the amount of the resource which a clan is given to start with
	 * @return int
	 */
	public function getInitialAmount ();
}
