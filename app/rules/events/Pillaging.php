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

		$resultMsg = '';
		if ($report->type === 'owner'){
			if ($data['successful']){
				if ($data['totalVictory']){
					$resultMsg = 'Úplné vítězství! Loupeživý útok se nebývale zdařil a obránce byl drtivě poražen!';
				}
				else{
					$resultMsg = 'Vítězství! Loupeživý útok se zdařil!';
				}
			}
			else{
				$resultMsg = 'Porážka! Přecenil jsi síly svého vojska a loupeživý útok se nezdařil';
			}
		}
		else{
			if ($data['successful']){
				if ($data['totalVictory']){
					$resultMsg = 'Úplná porážka! Tvůj klan se stal obětí loupeživého útoku a ani v nejmenším se neubránil!';
				}
				else{
					$resultMsg = 'Porážka! Tvůj klan se stal obětí loupeživého útoku a neubránil se!';
				}
			}
			else{
				$resultMsg = 'Vítězství! Tvůj klan se stal obětí loupeživého útoku ale snadno odvrátil nepřítelovy síly!';
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
