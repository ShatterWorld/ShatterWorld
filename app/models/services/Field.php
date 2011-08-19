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
		foreach ($this->getRepository()->findAll() as $object) {
			$this->delete($object, FALSE);
		}
		$this->entityManager->flush();
		
		for ($x = 0; $x < $this->options['sizeX']; $x++) {
			for ($y = 0; $y < $this->options['sizeY']; $y++) {
				$values = array();
				$types = array('forest', 'mountains', 'desert', 'plain', 'lake');
				$values['coords'] = array($x, $y);
				$values['type'] = $types[array_rand($types)];
				$this->create($values, FALSE);
			}
		}
		$this->entityManager->flush();
	}
}