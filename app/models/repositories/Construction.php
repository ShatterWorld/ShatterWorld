<?php
namespace Repositories;
use Entities;
use Nette\Utils\Neon;

class Construction extends BaseRepository
{
	public function getUsedUnitSlots (Entities\Clan $clan)
	{
		return $this->getUsedUnitSlotsFromData($this->findBy(array('owner' => $clan->id, 'type' => 'unitTraining')));
	}
	
	public function getUsedUnitSlotsByField (Entities\Field $field)
	{
		$result = $this->getUsedUnitSlotsFromData($this->findBy(array('target' => $field->id, 'owner' => $field->owner->id, 'type' => 'unitTraining')));
		return isset($result[$field->facility]) ? $result[$field->facility] : 0;
	}
	
	protected function getUsedUnitSlotsFromData ($data)
	{
		$result = array();
		foreach ($data as $training) {
			foreach (array_keys(Neon::decode($training->construction)) as $unit) {
				foreach ($this->context->rules->get('unit', $unit) as $slot => $amount) {
					if (array_key_exists($slot, $result)) {
						$result[$slot] = $result[$slot] + $amount;
					} else {
						$result[$slot] = $amount;
					}
				}
			}
		}
		return $result;
	}
}