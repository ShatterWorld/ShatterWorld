<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Entities;
use Nette\Diagnostics\Debugger;
use ReportItem;
use DataRow;

class UnitMovement extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Přesun jednotek';
	}

	public function process (Entities\Event $event, $processor)
	{
		$result = array(
			'units' => array()
		);

		foreach ($event->getUnits() as $unit) {
			$result['units'][$unit->type] = $unit->count;
		}

		$this->getContext()->model->getUnitService()->moveUnits($event->target, $event->owner, $event->units);
		return $result;
	}

	public function isValid (Entities\Event $event)
	{
		return ($event->target->owner === $event->origin->owner) || ($event->target->owner->alliance === $event->origin->owner->alliance);
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Přesun jednotek z %s na %s', $event->origin->getCoords(), $event->target->getCoords());
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
