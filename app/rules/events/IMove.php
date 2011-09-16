<?php
namespace Rules\Events;
use Entities;

interface IMove extends IEvent
{
	/**
	 * Is move from origin to target valid?
	 * @param Entities\Field
	 * @param Entities\Field
	 * @return bool
	 */
	public function isValid (Entities\Field $origin, Entities\Field $target);
}