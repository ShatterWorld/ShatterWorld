<?php
namespace Rules\Events;
use ReportItem;
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
		$message = array(
			new ReportItem('text', $data['successful'] ? 'Vítězství' : 'Porážka'),
			new ReportItem('unitGrid', $data['attacker']['casualties'], 'Ztráty')
		);
		if ($data['attacker']['loot']) {
			$message[] = new ReportItem('resourceGrid', $data['attacker']['loot'], 'Kořist');
		}
		return $message;
	}
}