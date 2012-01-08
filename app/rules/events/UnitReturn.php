<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;
use Nette\Diagnostics\Debugger;
use ReportItem;
use DataRow;

class UnitReturn extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Návrat jednotek';
	}

	public function process (Entities\Event $event, $processor)
	{
		$result = array();
		$units = array();
		foreach ($event->getUnits() as $unit) {
			$rule = $this->getContext()->rules->get('unit', $unit->type);
			$capacity = $capacity + $unit->count * $rule->getCapacity();
			$units[$unit->type] = $unit->count;
		}
		$this->getContext()->model->getUnitService()->moveUnits($event->target, $event->owner, $event->getUnits());
		if ($event->cargo) {
			$this->getContext()->model->getResourceService()->increase($event->owner, $event->cargo, $event->term);
		}
		return array('units' => $units);
	}

	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Návrat jednotek z %s do %s', $event->origin->getCoords(), $event->target->getCoords());
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;
		$message = array(
			ReportItem::create('unitGrid', array(
				DataRow::from($data['units'])->setLabel('Množství')
			))->setHeading('Jednotky'));

		return $message;
	}
}
