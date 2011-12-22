<?php
namespace Rules\Resources;
use Rules\AbstractRule;
use Entities;

class Fuel extends AbstractRule implements IExhaustableResource
{
	public function getDescription ()
	{
		return 'Palivo';
	}

	public function processExhaustion (Entities\Event $event)
	{

	}

	public function getExhaustionExplanation ()
	{
		return "Vyčerpání paliva";
	}

	public function formatExhaustionReport (Entities\Report $report)
	{
		return array();
	}

	public function getValue ()
	{
		return 0.05;
	}
}
