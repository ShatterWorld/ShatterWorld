<?php
namespace Rules\Events;
use ReportItem;
use Entities;

class Occupation extends Attack
{
	public function getDescription ()
	{
		return 'Dobyvačný útok';
	}

	public function process (Entities\Event $event, $processor)
	{
		$result = parent::process($event, $processor);
		if ($result['successful']) {
			if ($result['totalVictory']) {
				if ($loot = $result['attacker']['loot']) {
					$this->getContext()->model->getResourceService()->pay($event->target->owner, $loot, $event->term, FALSE);
					$this->getContext()->model->getResourceService()->increase($event->owner, $loot, $event->term, FALSE);
				}
				$this->getContext()->model->getFieldService()->setFieldOwner($event->target, $event->owner);
			}
			if ($facility = $event->target->facility) {
				$this->damageTargetFacility($event, 1, $processor);
				if ($result['totalVictory']) {
					$value = 0;
					$rule = $this->getContext()->rules->get('facility', $facility->level);
					for ($i = $facility->level; $i > 0; $i--) {
						$value += $rule->getValue($i);
					}
					$this->getContext()->model->resourceService->recalculateProduction($event->owner, $event->term);
					$this->getContext()->model->resourceService->recalculateProduction($event->target->owner, $event->term);
					$this->getContext()->model->scoreService->decreaseClanScore($event->target->owner, 'facility', $value);
					$this->getContext()->model->scoreService->increaseClanScore($event->owner, 'facility', $value);
				}
			}
		} else {
			$this->returnAttackingUnits($event, $processor);
		}
		return $result;
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		if ($report->type === 'owner') {
			if ($data['successful'] && $data['totalVictory']) {
				$resultMsg = sprintf('Úplné vítězství! Dobyvačný útok se zdařil a obránce %s byl drtivě poražen! Nepřítelovo území bylo násilně připojeno!', $data['defender']['name']);
			} else {
				$resultMsg = sprintf('Porážka! Přecenil jsi síly svého vojska a dobyvačný útok na klan %s se nezdařil', $data['defender']['name']);
			}
		} else {
			if ($data['successful'] && $data['totalVictory']) {
				$resultMsg = sprintf('Úplná porážka! Klan %s podnikl dobyvačný útok na tvoje území násilně připojil tvoje území ke svému!', $data['defender']['name']);
			} else {
				$resultMsg = sprintf('Vítězství! Odvrátil jsi dobyvačný útok klanu %s na tvoje území a rozloha klanu zůstala nezměněna!', $data['defender']['name']);
			}
		}

		$message = array(ReportItem::create('text', $resultMsg));
		$message = array_merge($message, parent::formatReport($report));
		if ($data['totalVictory'] && $data['attacker']['loot']) {
			$message[] = ReportItem::create('resourceGrid', array($data['attacker']['loot']))->setHeading('Kořist');
		}
		return $message;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Dobyvačný útok na %s', $event->target->getCoords());
	}

	public function isValid (Entities\Event $event)
	{
		if (parent::isValid($event)) {
			$valid = FALSE;
			foreach ($this->getContext()->model->getFieldRepository()->getFieldNeighbours($event->target) as $neighbour) {
				if ($neighbour->owner === $event->owner) {
					$valid = TRUE;
					break;
				}
			}
			return $valid;
		} else {
			return FALSE;
		}
	}
}
