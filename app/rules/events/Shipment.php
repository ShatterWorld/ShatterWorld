<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;
use Nette\Diagnostics\Debugger;
use ReportItem;

class Shipment extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Zásilka surovin';
	}

	public function process (Entities\Event $event, $processor)
	{
		$this->getContext()->model->getResourceService()->increase($event->target->owner, $event->cargo, $event->term);
		return array('cargo' => $event->cargo);
	}

	public function isValid (Entities\Event $event)
	{
		return true;
	}

	public function getExplanation (Entities\Event $event)
	{
		return sprintf('Zásilka surovin do %s', $event->target->getCoords());
	}

	public function formatReport (Entities\Report $report)
	{
		$data = $report->data;

		$resultMsg = '';
		if ($report->type === 'owner'){
			$resultMsg = 'Naše zboží dorazilo k obchodním partnerům.';
		}
		else{
			$resultMsg = 'Naši obchodní partneři k nám zavezli zboží.';
		}

		$message = array(ReportItem::create('text', $resultMsg));
		$message[] = ReportItem::create('resourceGrid', array($data['cargo']))->setHeading('Zboží');
		return $message;
	}
}
