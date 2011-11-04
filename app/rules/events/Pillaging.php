<?php
namespace Rules\Events;
use ReportItem;
use DataRow;
use Entities;

class Pillaging extends Attack
{
	public function getDescription ()
	{
		return 'Loupežný útok';
	}
	
	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		$message = array(ReportItem::create('text', $data['successful'] ? 'Vítězství' : 'Porážka'));
		return array_merge($message, parent::formatReport($report));
	}
}