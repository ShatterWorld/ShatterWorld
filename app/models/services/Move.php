<?php
namespace Services;
use Entities;

class Move extends Event
{
	public function startColonisation (Entities\Field $target, Entities\Clan $clan)
	{
		$this->create(array(
			'owner' => $clan,
			'target' => $target,
			'origin' => $clan->getHeadquarters(),
			'type' => 'colonisation',
			'timeout' => $this->context->stats->getColonisationTime($target, $clan)
		));
	}
}