<?php
namespace Services;
use Entities;

class Field extends BaseService {

	protected $options;

	protected $fieldRules;

	public function __construct ($context, $entityClass)
	{
		parent::__construct($context, $entityClass);
		$this->options = $context->params['game']['map'];
	}

	public function invalidateVisibleFields ($clanId)
	{
		$this->getRepository()->getVisibleFieldsCache()->save($clanId, NULL);
	}

	public function createMap ()
	{
		$map = array();
		for ($x = 1; $x <= $this->options['size']; $x++) {
			$map[$x] = array();
			for ($y = 1; $y <= $this->options['size']; $y++) {
				$map[$x][$y] = $this->pickType($x, $y, $map);
			}
		}
		foreach ($map as $x => $row) {
			foreach ($row as $y => $type) {
				$this->create(array('coords' => array($x, $y), 'type' => $type), FALSE);
			}
		}
		$this->entityManager->flush();
	}

	protected function getFieldRules ()
	{
		if (!isset($this->fieldRules)) {
			$this->fieldRules = $this->context->rules->getAll('field');
		}
		return $this->fieldRules;
	}

	protected function getProbabilityTable ($exclude = array())
	{
		$table = array();
		$peak = 0;
		foreach ($this->getFieldRules() as $type => $field) {
			if (!in_array($type = lcfirst($type), $exclude)) {
				$table[$peak + $field->probability] = $type;
				$peak = $peak + $field->probability;
			}
		}
		return array($table, $peak);
	}

	protected function pickType ($x, $y, &$map)
	{
		$neighbours = array();
		if (isset($map[$x][$y - 1])) $neighbours[] = $map[$x][$y - 1];
		if (isset($map[$x - 1][$y])) $neighbours[] = $map[$x - 1][$y];
		if (isset($map[$x - 1][$y + 1])) $neighbours[] = $map[$x - 1][$y + 1];
		$exclude = array();
		$excludeCount = 0;
		shuffle($neighbours);
		foreach ($neighbours as $field) {
			if (!in_array($field, $exclude)) {
				$exclude[] = $field;
				$excludeCount++;
			}
			if ($excludeCount >= $this->options['diversityMinimum']) {
				break;
			}
		}
		list($table, $peak) = $this->getProbabilityTable($exclude);
		$rand = mt_rand(0, $peak);
		foreach ($table as $probability => $type) {
			if ($rand <= $probability) {
				return $type;
			}
		}
	}
	public function setFieldOwner ($field, $newOwner)
	{
		$oldOwner = $field->owner;
		if ($oldOwner !== $newOwner) {
			$value = $this->context->rules->get('field', $field->type)->getValue();
			if ($newOwner !== null){
				$this->context->model->getScoreService()->increaseClanScore($newOwner, 'territory', $value);
			}
			if ($oldOwner !== null){
				$this->context->model->getScoreService()->decreaseClanScore($oldOwner, 'territory', $value);
			}
			foreach ($this->context->model->getConstructionRepository()->findBy(array('target' => $event->target->id)) as $construction) {
				$construction->failed = TRUE;
			}
			$this->update($field, array('owner' => $newOwner));
		}
	}
}
