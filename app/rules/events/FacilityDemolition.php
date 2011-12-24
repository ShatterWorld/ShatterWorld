<?php
namespace Rules\Events;
use Entities;
use ReportItem;
use Rules\AbstractRule;
use Nette\Diagnostics\Debugger;

class FacilityDemolition extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Bourání budovy';
	}

	public function process (Entities\Event $event, $processor)
	{
		$beforeLevel = $event->target->level;
		$changes = array('level' => $event->level);
		if ($event->level === 0) {
			$changes['facility'] = NULL;
		}

		$value = 0;
		$i = $event->target->level;
		$rule = $this->getContext()->rules->get('facility', $event->construction);
		while ($i > $event->level){
			$value += $rule->getValue($i);
			$i--;
		}
		$this->getContext()->model->getScoreService()->decreaseClanScore($event->owner, 'facility', $value);

		$this->getContext()->model->getFieldService()->update($event->target, $changes);
		$this->getContext()->model->getResourceService()->recalculateProduction($event->owner, $event->term);


		return array();
	}

	public function isValid (Entities\Event $event)
	{
		$clan = $this->getContext()->model->getClanRepository()->getPlayerClan();
		return $event->target->owner == $clan && ($event->level === 0 || ($event->target->level - 1 === $event->level));
	}

	public function getExplanation (Entities\Event $event)
	{
		if ($event->level) {
			return sprintf('Downgrade budovy %s (%d) na poli %s', $this->getContext()->rules->get('facility', $event->construction)->getDescription(), $event->level, $event->target->getCoords());
		} else {
			return sprintf('Demolice budovy %s na poli %s', $this->getContext()->rules->get('facility', $event->construction)->getDescription(), $event->target->getCoords());
		}
	}

	public function formatReport (Entities\Report $report)
	{
		$facility = $this->getContext()->rules->get('facility', $report->event->construction)->getDescription();
		$level = $report->event->level;
		return array(
			ReportItem::create('text', $level == 0 ? sprintf("Budova %s byla zbořena.", $facility) : sprintf("Úroveň budovy %s byla snížena na %s.", $facility, $level))
		);
	}

	public function isExclusive ()
	{
		return TRUE;
	}
}
