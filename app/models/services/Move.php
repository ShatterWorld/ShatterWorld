<?php
namespace Services;
use Entities;

class Move extends BaseService
{
	public function create ($values, $flush = TRUE)
	{
		$values['event'] = $this->context->model->getEventService()->create(array(
			'type' => $values['type'],
			'owner' => $values['clan'],
			'term' => $values['term']
		));
		parent::create($values, $flush);
	}
	
	public function startColonisation (Entities\Field $target, Entities\Clan $clan)
	{
		if ($this->context->rules->get('event', 'colonisation')->isValid($clan->getHeadquarters(), $target)) {
			$this->create(array(
				'clan' => $clan,
				'target' => $target,
				'origin' => $clan->getHeadquarters(),
				'type' => 'colonisation',
				'term' => $this->context->stats->getColonisationTime($target, $clan)
			));
		}
	}
}