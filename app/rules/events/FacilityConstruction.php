<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use Entities;

class FacilityConstruction extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Stavba budovy';
	}

	public function process (Entities\Event $event, $processor)
	{
		if ($event->target->facility) {
			$event->target->facility->setLevel($event->level);
		} else {
			$this->getContext()->model->facilityService->create(array(
				'type' => $event->construction,
				'level' => $event->level,
				'location' => $event->target
			), FALSE);
		}
		$this->getContext()->model->getResourceService()->recalculateProduction($event->owner, $event->term);

		$value = $this->getContext()->rules->get('facility', $event->construction)->getValue($event->level);
		$this->getContext()->model->getScoreService()->increaseClanScore($event->owner, 'facility', $value);

		return array();
	}

	public function isValid (Entities\Event $event)
	{
		return $event->target->owner === $event->owner && $event->level <= $this->getContext()->params['game']['stats']['facilityLevelCap'] &&
			(($event->target->facility && $event->target->facility->type === $event->construction && $event->target->facility->level === ($event->level - 1)) || ($event->target->facility === NULL && $event->level === 1));
	}

	public function isRemote ()
	{
		return FALSE;
	}

	public function getExplanation (Entities\Event $event)
	{
		if ($event->level) {
			return sprintf('Stavba budovy %s (%d) na poli %s', $this->getContext()->rules->get('facility', $event->construction)->getDescription(), $event->level, $event->target->getCoords());
		} else {
			return sprintf('Stavba budovy %s na poli %s', $this->getContext()->rules->get('facility', $event->construction)->getDescription(), $event->target->getCoords());
		}
	}

	public function formatReport (Entities\Report $report)
	{
		$facility = $this->getContext()->rules->get('facility', $report->event->construction)->getDescription();
		$level = $report->event->level;
		return array(
			ReportItem::create('text', $level == 1 ? sprintf("Budova %s byla dostavena.", $facility) : sprintf("Budova %s byla vylepšena na úroveň %s.", $facility, $level))
		);
	}

	public function isExclusive ()
	{
		return TRUE;
	}
}
