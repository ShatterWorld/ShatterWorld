<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use Entities;

class Research extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Výzkum';
	}

	public function isValid (Entities\Event $event)
	{
		return true;
		//return isset($this->getConstructionRepository()->getRunningResearches($this->getPlayerClan())[$event->construction])

	}

	public function process (Entities\Event $event, $processor)
	{
		if ($event->level > 1) {
			$research = $this->getContext()->model->getResearchRepository()->findOneBy(array('type' => $event->construction, 'owner' => $event->owner->id));
			$research->level = $event->level;
		} else {
			$research = $this->getContext()->model->getResearchService()->create(array(
				'type' => $event->construction,
				'owner' => $event->owner,
				'level' => $event->level
			), FALSE);
		}
		$rule = $this->getContext()->rules->get('research', $event->construction);
		$rule->afterResearch($event);
		return array('type' => $event->type, 'level' => $event->level);
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		$rule = $this->getContext()->rules->get('research', $data->type);
		return array(
			ReportItem::create('text', $rule->getDescription())->setHeading('Výzkum dokončen'),
		);
	}

	public function isExclusive ()
	{
		return true;
	}
}
