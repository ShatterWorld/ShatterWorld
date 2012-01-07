<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;
use ReportItem;
use DataRow;

class Razing extends Attack implements IEvent
{
	public function getDescription ()
	{
		return 'Ničivý útok';
	}

	public function process (Entities\Event $event, $processor)
	{
		$result = parent::process($event, $processor);
		if ($result['successful']) {
			$dmg = 1;
			if ($result['totalVictory']) {
				$dmg += 2;
			}
			$this->damageTargetFacility($event, $dmg, $processor);
		}
		$this->returnAttackingUnits($event, $processor, $result['attacker']['loot']);
		return $result;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Ničivý útok na %s', $event->target->getCoords());
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;

		$resultMsg = '';
		if ($report->type === 'owner'){
			if ($data['successful']){
				if ($data['totalVictory']){
					$resultMsg = 'Úplné vítězství! Ničivý útok se nebývale zdařil a obránce byl drtivě poražen!';
				}
				else{
					$resultMsg = 'Vítězství! Ničivý útok se zdařil!';
				}
			}
			else{
				$resultMsg = 'Porážka! Přecenil jsi síly svého vojska a ničivý útok se nezdařil';
			}
		}
		else{
			if ($data['successful']){
				if ($data['totalVictory']){
					$resultMsg = 'Úplná porážka! Tvůj klan se stal obětí ničivého útoku a ani v nejmenším se neubránil!';
				}
				else{
					$resultMsg = 'Porážka! Tvůj klan se stal obětí ničivého útoku a neubránil se!';
				}
			}
			else{
				$resultMsg = 'Vítězství! Tvůj klan se stal obětí ničivého útoku ale snadno odvrátil nepřítelovy síly!';
			}
		}

		$message = array(ReportItem::create('text', $resultMsg));
		$message = array_merge($message, parent::formatReport($report));
		if ($data['successful'] && $data['attacker']['loot']) {
			$message[] = ReportItem::create('resourceGrid', array($data['attacker']['loot']))->setHeading('Kořist');
		}
		return $message;
	}

	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}
}
