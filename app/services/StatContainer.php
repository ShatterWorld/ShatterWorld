<?php

/**
 * A game stat container, which provides access to values which are results of rules interaction
 * @author Jan "Teyras" Buchar
 */
class StatContainer extends Nette\Object
{
	/** @var Nette\DI\Container */
	protected $context;
	
	/**
	 * Constructor
	 * @param Nette\DI\Container
	 */
	public function __construct (Nette\DI\Container $context)
	{
		$this->context = $context;
	}
	
	/**
	 * Returns how many fields in the distance can the clan see from its territory
	 * @param Entities\Clan
	 * @return int
	 */
	public function getVisibilityRadius (Entities\Clan $clan)
	{
		return $this->context->params['game']['stats']['baseLOS'];
	}
	
	/**
	 * Calculate the time needed to colonise a field
	 * @param Entities\Field
	 * @param Entities\Field
	 * @return int
	 */
	public function getColonisationTime ($origin, $target)
	{
		$base = $this->context->params['game']['stats']['baseColonisationTime'];
		$distance = $this->context->model->getFieldRepository()->calculateDistance($origin, $target);
		$count = $this->context->model->getFieldRepository()->getTerritorySize($origin->owner);
		return $base * $distance * $count;
	}
	
	/**
	 * Get given clan's production of given resource
	 * @param Entities\Clan
	 * @param string
	 * @return int
	 */
	public function getResourceProduction (Entities\Clan $clan, $resource)
	{
		$result = 0;
		foreach ($this->context->model->getFieldRepository()->findByOwner($clan) as $field) {
			if ($field->facility) {
				$production = $this->context->rules->get('facility', $field->facility)->getProduction($field->level);
				$fieldBonus = $this->context->rules->get('field', $field->type)->getProductionBonus();
				$modifier = 1;
				if (array_key_exists($resource, $fieldBonus)) {
					$modifier = $modifier + $fieldBonus[$resource] / 100;
				}
				if (array_key_exists($resource, $production)) {
					$result = $result + $modifier * $production[$resource];
				}
			}
		}
		return $result;
	}
}