<?php
namespace Rules\Events;
use Entities;

interface IConstruction extends IEvent
{
	/**
	 * Is construction possible on given field?
	 * @param Entities\Field
	 * @param string
	 * @param int
	 * @return bool
	 */
	public function isValid ($type, $level, Entities\Field $field = NULL);
}