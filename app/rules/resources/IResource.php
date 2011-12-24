<?php
namespace Rules\Resources;
use Rules\IRule;
use Entities;


interface IResource extends IRule
{
	/**
	 * Value
	 * @param Entities\Resource
	 * @return float
	 */
	public function getValue (Entities\Resource $resource);
}
