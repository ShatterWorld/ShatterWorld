<?php
namespace Rules\Resources;
use Entities;

interface IExhaustableResource extends IResource
{
	/**
	 * Process exhaustion
	 * @param Entities\Clan
	 * @return void
	 */
	public function processExhaustion (Entities\Event $event);
	
	/**
	 * Get an explanation of this resource's exhaustion
	 * @return string
	 */
	public function getExhaustionExplanation ();
	
	/**
	 * Format report for exhaustion event
	 * @param Entities\Report
	 * @return array of ReportItem
	 */
	public function formatExhaustionReport (Entities\Report $report);
}