<?php
namespace Rules\Events;
use Entities;

/**
 * A game event
 * @author Jan "Teyras" Buchar
 */
interface IEvent extends \Rules\IRule {
	
	/**
	 * Process the event
	 * @param Entities\Event
	 * @return void
	 */
	public function process (Entities\Event $event);
	
	/**
	 * Is the event valid?
	 * @param Entities\Event
	 * @return bool
	 */
	public function isValid (Entities\Event $event);
}
