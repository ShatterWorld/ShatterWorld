<?php
namespace Rules\Events;

interface IConstruction extends IEvent
{
	/**
	 * Returns true if the event can't happen on a field with another exclusive event
	 *
	 */
	public function isExclusive ();
}