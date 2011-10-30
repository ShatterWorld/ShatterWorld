<?php
namespace Rules\Events;
use Rules\AbstractRule;
use ReportItem;
use Entities;

class Exploration extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Průzkum';
	}

	public function process (Entities\Event $event)
	{
		$cargo = array();
		foreach($this->getContext()->rules->get('field', $event->target->type)->getProductionBonuses() as $name => $resource){
			if ($resource > 0){
				$cargo[$name] = floor($resource * rand($this->getContext()->params['game']['stats']['minExplorationCoefficient'], $this->getContext()->params['game']['stats']['minExplorationCoefficient']));
			}
		}
		$this->getContext()->model->getShipmentService()->create(array(
			'type' => 'shipment',
			'timeout' => 120 * $this->getContext()->model->getFieldRepository()->calculateDistance($event->owner->getHeadquarters(), $event->target),
			'owner' => $event->owner,
			'origin' => $event->target,
			'target' => $event->owner->getHeadquarters(),
			'cargo' => $cargo
		), FALSE);

		return $cargo;
	}

	public function isValid (Entities\Event $event)
	{
		return ($event->target->owner === NULL) ? true : false;
	}

	public function formatReport (Entities\Report $report)
	{
		return array(
			new ReportItem('text', 'Prozkoumáno!')
		);
	}

	public function isExclusive ()
	{
		return false;
	}
}
