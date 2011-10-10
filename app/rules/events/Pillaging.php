<?php
namespace Rules\Events;
use Entities;

class Pillaging extends Attack
{
	public function getDescription ()
	{
		return 'Loupežný útok';
	}
	
	public function process (Entities\Event $event)
	{
		$result = $this->evaluateBattle($event);
	}
}