<?php
namespace Repositories;
use Entities;
use Nette\Utils\Json;

class Construction extends BaseRepository
{
	public function getUsedUnitSlots (Entities\Clan $clan)
	{
		$result = array();
		foreach ($this->findBy(array('owner' => $clan->id, 'type' => 'unitTraining')) as $training) {
			foreach (array_keys(Json::decode($training->construction)) as $unit) {
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