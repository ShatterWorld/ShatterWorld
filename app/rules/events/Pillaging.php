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
		$message = array(
			ReportItem::create('text', $data['successful'] ? 'Vítězství' : 'Porážka'),
			ReportItem::create('unitGrid', array(
				DataRow::from($data['attacker']['units'])->setLabel('Jednotky'),
				DataRow::from($data['attacker']['casualties'])->setLabel('Ztráty')
			))->setHeading('Útočník'),
			ReportItem::create('unitGrid', array(
				DataRow::from($data['defender']['units'])->setLabel('Jednotky'),
				DataRow::from($data['defender']['casualties'])->setLabel('Ztráty')
			))
		);
		if ($data['attacker']['loot']) {
			$message[] = ReportItem::create('resourceGrid', $data['attacker']['loot'], 'Kořist');
		}
		return $message;
	}
}