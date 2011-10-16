<?php
namespace Rules\Events;
use Entities;

/**
 * A game event
 * @author Jan "Teyras" Buchar
 */
interface IEvent extends \Rules\IRule {
	
	/**
	 * Process the event and return report data
	 * @param Entities\Event
	 * @return array|object
	 */
	public function process (Entities\Event $event);
	
	/**
	 * Is the event valid?
	 * @param Entities\Event
	 * @return bool
	 */
	public function isValid (Entities\Event $event);
	
	/**
	 * Formats report data into a whatever TODO: decide about the format
	 * @param object
	 * @return string
	 */
	public function formatReport ($data);
}
