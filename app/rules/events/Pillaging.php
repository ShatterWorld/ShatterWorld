<?php
namespace Rules\Events;
use ReportItem;
use DataRow;
use Entities;
use Nette\Diagnostics\Debugger;

class Pillaging extends Attack
{
	public function getDescription ()
	{
		return 'Loupežný útok';
	}

	public function process (Entities\Event $event, $processor)
	{
		$result = parent::process($event, $processor);
		$this->returnAttackingUnits($event, $processor, $result['attacker']['loot']);
		return $result;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Loupeživý útok na %s', $event->target->getCoords());
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		if ($report->type === 'owner') {
			if ($data['successful']){
				if ($data['totalVictory']) {
					$resultMsg = sprintf('Úplné vítězství! Loupeživý útok na klan %s se nebývale zdařil a obránce byl drtivě poražen!', $data['defender']['name']);
				} else {
					$resultMsg = sprintf('Vítězství! Loupeživý útok na klan %s se zdařil!', $data['defender']['name']);
				}
			} else {
				$resultMsg = sprintf('Porážka! Přecenil jsi síly svého vojska a loupeživý útok na klan %s se nezdařil', $data['defender']['name']);
			}
		} else {
			if ($data['successful']) {
				if ($data['totalVictory']) {
					$resultMsg = sprintf('Úplná porážka! Tvůj klan se stal obětí loupeživého útoku klanu %s a ani v nejmenším se neubránil!', $data['attacker']['name']);
				} else {
					$resultMsg = sprintf('Porážka! Tvůj klan se stal obětí loupeživého útoku klanu %s a neubránil se!', $data['attacker']['name']);
				}
			} else {
				$resultMsg = sprintf('Vítězství! Tvůj klan se stal obětí loupeživého útoku klanu %s, ale snadno odvrátil nepřítelovy síly!', $data['attacker']['name']);
			}
		}
		$message = array(ReportItem::create('text', $resultMsg));
		$message = array_merge($message, parent::formatReport($report));
		if ($data['successful'] && $data['attacker']['loot']) {
			$message[] = ReportItem::create('resourceGrid', array($data['attacker']['loot']))->setHeading('Kořist');
		}
		return $message;
	}
}
