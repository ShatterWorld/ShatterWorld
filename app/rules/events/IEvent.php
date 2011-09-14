<?php
namespace Rules\Events;

/**
 * A game event
 * @author Jan "Teyras" Buchar
 */
interface IEvent extends \Rules\IRule {
	
	/**
	 * Process the event with given event ID
	 * @param int
	 * @return void
	 */
	public function process ($id);
	
	/**
	 * Get information about event with given event id
	 * @param int
	 * @return array
	 */
	public function getInfo ($eventId);
}
