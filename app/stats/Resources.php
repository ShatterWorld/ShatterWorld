<?php
namespace Stats;
use Entities;

class Resources extends AbstractStat
{
	protected function getCoefficient (Entities\Clan $clan, $type)
	{
		$level = $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, $type);

		if ($level == 0){
			return 1;
		}
		return 1 + pow($level + 1, 2)/100;
	}
	
	/**
	 * Get given clan's production of resources
	 * @param Entities\Clan
	 * @return array of float
	 */
	public function getProduction (Entities\Clan $clan)
	{
		$result = array();
		foreach ($this->getContext()->model->getFieldRepository()->findByOwner($clan->id) as $field) {
			if ($field->facility) {
				$production = $this->getContext()->rules->get('facility', $field->facility)->getProduction($field->level);
				$fieldBonus = $this->getContext()->rules->get('field', $field->type)->getProductionBonuses();
				foreach ($production as $resource => $value) {
					$modifier = $this->getCoefficient($clan, $resource);
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
		foreach ($this->getContext()->model->getUnitRepository()->findByOwner($clan->id) as $unit) {
			$rule = $this->getContext()->rules->get('unit', $unit->type);
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

	public function getStorage (Entities\Clan $clan)
	{
		$storage = $this->getContext()->params['game']['stats']['baseStorage'];
		$research = $this->getContext()->model->getResearchRepository()->findOneBy(array(
			'type' => 'storage',
			'owner' => $clan->id
		));
		if ($research) {
			$storage = $storage * pow($research->level + 1, 2) / 2;
		}
		return $storage;
	}
}