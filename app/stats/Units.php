<?php
namespace Stats;
use Entities;

class Units extends AbstractStat
{
	public function getSlots (Entities\Clan $clan)
	{
		$result = array();
		foreach ($this->getContext()->model->getFieldRepository()->findByOwner($clan->id) as $clanField) {
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
}