<?php
namespace Stats;
use Rules;
use Entities;

class Units extends AbstractStat
{
	public function getSlots (Entities\Clan $clan)
	{
		$result = array();
		foreach ($this->getContext()->model->fieldRepository->findByOwner($clan->id) as $clanField) {
			$facility = $clanField->facility;
			if ($facility !== NULL) {
				$rule = $this->getContext()->rules->get('facility', $facility);
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

	public function getUnitLevel ($clan, $unitName)
	{
		return $this->getContext()->model->getResearchRepository()->getResearchLevel($clan, $unitName);
	}

	public function getUnitAttack ($clan, $unitName)
	{
		$rule = $this->getContext()->rules->get('unit', $unitName);
		$level = $this->getUnitLevel($clan, $unitName);
		return $rule->getAttack() + $level;
	}

	public function getUnitDefense ($clan, $unitName)
	{
		$rule = $this->getContext()->rules->get('unit', $unitName);
		$level = $this->getUnitLevel($clan, $unitName);
		return $rule->getDefense() + $level;
	}

	public function getSpyForce ($clan)
	{
		$level = $this->getUnitLevel($clan, 'spy');
		return $level;
	}


}

