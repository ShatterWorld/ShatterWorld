<?php
namespace Services;

class Field extends BaseService {
	
	protected $options;
	
	public function __construct ($context, $entityClass)
	{
		parent::__construct($context, $entityClass);
		$this->options = $context->params['game']['map'];
	}
	
	public function createMap ()
	{
		$probabilityTable = array();
		$probabilityPeak = 0;
		
		foreach ($this->context->rules->getAll('field') as $type => $field) {
			$probabilityTable[$probabilityPeak + $field->probability] = lcfirst($type);
			$probabilityPeak = $probabilityPeak + $field->probability;
		}
		$pickRandomType = function () use ($probabilityTable, $probabilityPeak) {
			$rand = mt_rand(0, $probabilityPeak);
			foreach ($probabilityTable as $probability => $type) {
				if ($rand <= $probability) {
					return $type;
				}
			}
		};
		for ($x = 0; $x < $this->options['size']; $x++) {
			for ($y = 0; $y < $this->options['size']; $y++) {
				$values = array();
				$values['coords'] = array($x, $y);
				$values['type'] = $pickRandomType();
				$this->create($values, FALSE);
			}
		}
		$this->entityManager->flush();
	}
}