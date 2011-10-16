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
	
	protected function getColonisationCoefficient (Entities\Field $target, Entities\Clan $clan)
	{
		$distance = $this->context->model->getFieldRepository()->calculateDistance($clan->getHeadquarters(), $target);
		$count = $this->context->model->getFieldRepository()->getTerritorySize($clan) + $this->context->model->getConstructionRepository()->getColonisationCount($clan);
		return $distance * $count;
	}
	
	/**
	 * Calculate the time needed to colonise a field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	public function getColonisationTime (Entities\Field $target, Entities\Clan $clan)
	{
		$base = $this->context->params['game']['stats']['baseColonisationTime'];
		$coefficient = $this->getColonisationCoefficient($target, $clan);
		return $base * $coefficient;
	}
	
	public function getColonisationCost (Entities\Field $target, Entities\Clan $clan)
	{
		$coefficient = $this->getColonisationCoefficient($target, $clan);
		return array(
			'food' => 10 * $coefficient,
			'stone' => 30 * $coefficient,
			'metal' => 20 * $coefficient
		);
	}
	
	public function getAbandonmentTime ($level)
	{
		return $level * $this->context->params['game']['stats']['baseAbandonmentTime'];
	}
	
	/**
	 * Get given clan's production of resources
	 * @param Entities\Clan
	 * @return array of float
	 */
	public function getResourcesProduction (Entities\Clan $clan)
	{
		$result = array();
		foreach ($this->context->model->getFieldRepository()->findByOwner($clan->id) as $field) {
			if ($field->facility) {
				$production = $this->context->rules->get('facility', $field->facility)->getProduction($field->level);
				$fieldBonus = $this->context->rules->get('field', $field->type)->getProductionBonuses();
				$modifier = 1;
				foreach ($production as $resource => $value) {
					if (array_key_exists($resource, $fieldBonus)) {
						$modifier = $modifier + $fieldBonus[$resource] / 100;
					}
					if (array_key_exists($resource, $result)) {
						$result[$resource] = $result[$resource] + $modifier * $value;
					} else {
						$result[$resource] = $modifier * $value;
					}
				}
			}
		}
		foreach ($this->context->model->getUnitRepository()->findByOwner($clan->id) as $unit) {
			$rule = $this->context->rules->get('unit', $unit->type);
			foreach ($rule->getUpkeep() as $resource => $amount) {
				if (array_key_exists($resource, $result)) {
					$result[$resource] = $result[$resource] - $amount * $unit->count;
				} else {
					$result[$resource] = (-1) * $amount * $unit->count;
				}
			}
		}
		return $result;
	}
	
	public function getTotalUnitSlots (Entities\Clan $clan)
	{
		$result = array();
		foreach ($this->context->model->getFieldRepository()->findByOwner($clan->id) as $clanField) {
			$facility = $clanField->facility;
			if ($facility !== NULL) {
				$rule = $this->context->rules->get('facility', $facility);
				if ($rule instanceof Rules\Facilities\IConstructionFacility) {
					if (array_key_exists($facility, $result)) {
						$result[$facility] = $result[$facility] + $rule->getCapacity($clanField->level);
					} else {
						$result[$facility] = $rule->getCapacity($clanField->level);
					}
				}
			}
		}
		return $result;
	}
	
	public function getAvailableFacilities (Entities\Clan $clan)
	{
		$result = array();
		$buildings = $this->context->model->getFieldRepository()->getClanFacilities($clan);
		foreach ($this->context->rules->getAll('facility') as $name => $facility) {
			$depsSatisfied = TRUE;
			foreach ($facility->getDependencies() as $dep => $level) {
				if (!isset($buildings[$dep]) || $buildings[$dep] <= $level) {
					$depsSatisfied = FALSE;
					break;
				}
			}
			if ($depsSatisfied && $name !== 'headquarters') {
				$result[] = $name;
			}
		}
		return $result;
	}
}