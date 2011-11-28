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
	 * @param Services\Event
	 * @return array|object
	 */
	public function process (Entities\Event $event, $processor);
	
	/**
	 * Is the event valid?
	 * @param Entities\Event
	 * @return bool
	 */
	public function isValid (Entities\Event $event);
	
	/**
	 * Formats report data into an array
	 * @param Entities\Report
	 * @return array of ReportItem
	 */
	public function formatReport (Entities\Report $report);
	
	/**
	 * Get a description of the upcoming event
	 * @param Entities\Event
	 * @return string
	 */
	public function getExplanation (Entities\Event $event);
}
