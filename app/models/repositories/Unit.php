<?php
namespace Repositories;
use Entities;

class Unit extends BaseRepository
{
	public function findUnit (Entities\Clan $owner, Entities\Field $location, $type)
	{
		return $this->findOneBy(array(
			'owner' => $owner->id,
			'type' => $type,
			'location' => $location->id,
			'move' => NULL
		));
	}

	public function getClanUnits (Entities\Clan $owner)
	{
		return $this->findBy(array(
			'owner' => $owner->id,
			'move' => NULL
		));
	}
}
