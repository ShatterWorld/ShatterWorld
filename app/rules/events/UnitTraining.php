<?php
namespace Rules\Events;
use Rules\AbstractRule;
use Nette\Utils\Json;
use Entities;

class UnitTraining extends AbstractRule implements IConstruction
{
	public function getDescription ()
	{
		return 'Trénink jednotek';
	}

	public function process (Entities\Event $event)
	{
		$this->getContext()->model->getUnitService()->addUnits($event->target, $event->owner, Json::decode($event->construction, Json::FORCE_ARRAY));
		return array();
	}
	
	public function isValid (Entities\Event $event)
	{
		return TRUE;
	}
	
	public function formatReport ($data)
	{
		return $data;
	}
	
	public function isExclusive ()
	{
		return FALSE;
	}
}