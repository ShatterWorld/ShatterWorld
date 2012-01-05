<?php
namespace Stats;
use Rules;
use Entities;
use Nette\Diagnostics\Debugger;
/**
 * Units stat
 * @author Petr Bělohlávek
 */
class Units extends AbstractStat
{
	/**
	 * Get clan slots
	 * @param Entities\Clan
	 * @return array of int
	 */
	public function getSlots (Entities\Clan $clan)
	{
		$result = array();
		foreach ($this->getContext()->model->fieldRepository->findByOwner($clan->id) as $clanField) {
			$facility = $clanField->facility;
			if ($facility && !$facility->damaged) {
				$rule = $this->getContext()->rules->get('facility', $facility->type);
				Debugger::barDump($rule);
				if ($rule instanceof Rules\Facilities\IConstructionFacility) {
					if (array_key_exists($facility->type, $result)) {
						$result[$facility->type] = $result[$facility->type] + $rule->getCapacity($facility->level);
					} else {
						$result[$facility->type] = $rule->getCapacity($facility->level);
					}
				}
			}
		}
		return $result;
	}

	/**
	 * Get level of the unit
	 * @param Entities\Clan
	 * @param string
	 * @return int
	 */
	public function getUnitLevel (Entities\Clan $clan, $unitName)
	{
		return $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, $unitName);
	}

	/**
	 * Get attack of the unit
	 * @param Entities\Clan
	 * @param string
	 * @return int
	 */
	public function getUnitAttack (Entities\Clan $clan, $unitName)
	{
		$rule = $this->getContext()->rules->get('unit', $unitName);
		$level = $this->getUnitLevel($clan, $unitName);
		return $rule->getAttack() + $level;
	}

	/**
	 * Get total attack force of the clan
	 * @param Entities\Clan
	 * @return int
	 */
	public function getClanAttackForce (Entities\Clan $clan)
	{
		$units = $this->getContext()->model->getUnitRepository()->getClanUnits($clan);

		$attackPower = 0;
		foreach($units as $unit){
			$attackPower += $this->getUnitAttack($clan, $unit->type) * $unit->count;
		}
		return $attackPower;
	}

	/**
	 * Get defence of the unit
	 * @param Entities\Clan
	 * @param string
	 * @return int
	 */
	public function getUnitDefence (Entities\Clan $clan, $unitName)
	{
		$rule = $this->getContext()->rules->get('unit', $unitName);
		$level = $this->getUnitLevel($clan, $unitName);
		return $rule->getDefense() + $level;
	}

	/**
	 * Get defence of the unit
	 * @deprecated
	 * @param Entities\Clan
	 * @param string
	 * @return int
	 */
	public function getUnitDefense (Entities\Clan $clan, $unitName)
	{
		return $this->getUnitDefence($clan, $unitName);
	}

	/**
	 * Get total defence force of the clan
	 * @param Entities\Clan
	 * @return int
	 */
	public function getClanDefenceForce (Entities\Clan $clan)
	{
		$units = $this->getContext()->model->getUnitRepository()->getClanUnits($clan);
		$defencePower = 0;
		foreach($units as $unit){
			$defencePower += $this->getUnitDefense($clan, $unit->type) * $unit->count;
		}
		return $defencePower;
	}

	/**
	 * Get total spy force of the clan
	 * @param Entities\Clan
	 * @return int
	 */
	public function getSpyForce (Entities\Clan $clan)
	{
		$level = $this->getUnitLevel($clan, 'spy');
		$spyCount = $this->getContext()->model->getUnitRepository()->getTotalUnitCount($clan, 'spy');
		$territorySize = $this->getContext()->model->getFieldRepository()->getTerritorySize($clan);

		if ($territorySize <= 0){
			return 0;
		}

		return $level * $spyCount / $territorySize;
	}


}

