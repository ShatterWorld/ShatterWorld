<?php
use Nette\Diagnostics\Debugger;

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
	 * Get the count of available orders for given clan
	 * @param Entities\Clan
	 * @return int
	 */
	public function getAvailableOrders (Entities\Clan $clan)
	{
		$now = new \DateTime();
		$cap = $this->context->params['game']['stats']['orderCap'];
		$diff = $now->format('U') - $this->context->params['game']['start']->format('U');
		$orders = (intval($diff / $this->context->params['game']['stats']['orderTime'])) - $clan->orders->issued - $clan->orders->expired;
		if ($orders > $cap) {
			$this->context->model->getOrderService()->update($clan->orders, array(
				'expired' => $clan->orders->expired + ($orders - $cap)
			));
			return $cap;
		}
		return $orders;
	}

	/**
	 * Get the time remaining until another order is received
	 * @return int
	 */
	public function getOrderTimeout ()
	{
		$now = new \DateTime();
		$orderTime = $this->context->params['game']['stats']['orderTime'];
		return $orderTime - (($now->format('U') - $this->context->params['game']['start']->format('U')) % $orderTime);
	}
	
	/**
	 * Returns how many fields in the distance can the clan see from its territory
	 * @param Entities\Clan
	 * @return int
	 */
	public function getVisibilityRadius (Entities\Clan $clan)
	{
		$baseReconnaissance = $this->context->params['game']['stats']['baseLOS'];
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, 'reconnaissance');
		return $baseReconnaissance + $level;

	}

	/**
	 * Returns the colonisation coefficient
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	protected function getColonisationCoefficient (Entities\Field $target, Entities\Clan $clan)
	{
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, 'colonisationEfficiency');

		$distance = $this->context->model->getFieldRepository()->calculateDistance($clan->getHeadquarters(), $target);
		$count = $this->context->model->getFieldRepository()->getTerritorySize($clan) + $this->context->model->getConstructionRepository()->getColonisationCount($clan);
		return $distance * $count * (1 - pow($level, 2) / 100);
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

	/**
	 * Calculate the costs needed to colonise a field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	public function getColonisationCost (Entities\Field $target, Entities\Clan $clan)
	{
		$coefficient = $this->getColonisationCoefficient($target, $clan);
		return array(
			'food' => 10 * $coefficient,
			'stone' => 20 * $coefficient,
			'metal' => 20 * $coefficient
		);
	}

	/**
	 * Returns the exploration coefficient
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	protected function getExplorationCoefficient (Entities\Field $target, Entities\Clan $clan)
	{
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, 'explorationEfficiency');

		$distance = $this->context->model->getFieldRepository()->calculateDistance($clan->getHeadquarters(), $target);
		return ($distance - 1) * (1 - pow($level, 2) / 100);
	}

	/**
	 * Calculate the time needed to esplore a field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	public function getExplorationTime (Entities\Field $target, Entities\Clan $clan)
	{
		$base = $this->context->params['game']['stats']['baseExplorationTime'];
		$coefficient = $this->getExplorationCoefficient($target, $clan);
		return $base * ($coefficient+1);
	}

	/**
	 * Calculate the costs needed to colonise a field
	 * @param Entities\Field
	 * @param Entities\Clan
	 * @return int
	 */
	public function getExplorationCost (Entities\Field $target, Entities\Clan $clan)
	{
		$coefficient = $this->getExplorationCoefficient($target, $clan);
		return array(
			'food' => 10 * $coefficient,
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

	public function getResourceStorage (Entities\Clan $clan)
	{
		$storage = $this->context->params['game']['stats']['baseStorage'];
		$research = $this->context->model->getResearchRepository()->findOneBy(array(
			'type' => 'storage',
			'owner' => $clan->id
		));
		if ($research) {
			$storage = $storage * pow($research->level + 1, 2) / 2;
		}
		return $storage;
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

	/**
	 * Returns the number of mediators the offer can go through
	 * @param Entities\Clan
	 * @return int
	 */
	public function getTradingRadius (Entities\Clan $clan)
	{
		$baseRadius = $this->context->params['game']['stats']['baseTradingRadius'];
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, 'tradeRoutes');
		return $baseRadius + 2 * $level;
	}

	/**
	 * Returns the speed of merchants
	 * @param Entities\Clan
	 * @return int
	 */
	public function getMerchantSpeed (Entities\Clan $clan)
	{
		$baseMerchantSpeed = $this->context->params['game']['stats']['baseMerchantSpeed'];
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, 'tradeRoutes');
		return $baseMerchantSpeed * ($level + 1);
	}

	/**
	 * Returns the % profit of the clan
	 * @param Entities\Clan
	 * @return float
	 */
	public function getTradeProfit (Entities\Clan $clan)
	{
		$baseTradeProfit = $this->context->params['game']['stats']['baseTradeProfit'];
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, 'tradeProfit');
		return $baseTradeProfit * pow($level, 2);
	}

	/**
	 * Returns research efficiency
	 * @param Entities\Clan
	 * @return float
	 */
	public function getResearchEfficiency (Entities\Clan $clan)
	{
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, 'researchEfficiency');
		if ($level == 0){
			return 1;
		}
		return 1 - pow($level, 2)/100;
	}

	/**
	 * Returns the production coefficient
	 * @param Entities\Clan
	 * @param string
	 * @return float
	 */
	public function getProductionCoefficient (Entities\Clan $clan, $type)
	{
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, $type);

		if ($level == 0){
			return 1;
		}
		return 1 + pow($level + 1, 2)/100;
	}

	/**
	 * Returns the construction coefficient
	 * @param Entities\Clan
	 * @return float
	 */
	public function getConstructionCoefficient (Entities\Clan $clan)
	{
		$level = $this->context->model->getResearchRepository()->getResearchLevel($clan, 'constructionTechnology');

		if ($level == 0){
			return 1;
		}
		return 1 - pow($level, 2)/100;
	}

}
