<?php
namespace Rules\Events;
use Entities;
use Rules\AbstractRule;

class Shipment extends AbstractRule implements IEvent
{
	public function getDescription ()
	{
		return 'Zásilka surovin';
	}

	public function process (Entities\Event $event)
	{
		return array();
	}

	public function isValid (Entities\Event $event)
	{
		return true;
	}

	public function formatReport ($data)
	{
		return $data;
	}
}
