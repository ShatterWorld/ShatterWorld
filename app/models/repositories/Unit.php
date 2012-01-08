<?php
namespace Repositories;
use Entities;

class Unit extends BaseRepository
{
	/**
	 *	Return unit of type, owned by clan, located on field
	 * 	@param Entities\Clan
	 * 	@param Entities\Field
	 * 	@param string
	 *	@return Entities\Unit
	 */
	public function findUnit (Entities\Clan $owner, Entities\Field $location, $type)
	{
		return $this->findOneBy(array(
			'owner' => $owner->id,
			'type' => $type,
			'location' => $location->id,
			'move' => NULL
		));
	}

	/**
	 *	Return units owned by clan, which don't move
	 * 	@param Entities\Clan
	 *	@return array of Entities\Unit
	 */
	public function getStaticClanUnits (Entities\Clan $owner)
	{
		return $this->findBy(array(
			'owner' => $owner->id,
			'move' => NULL
		));
	}

	/**
	 *	Return all units owned by clan
	 * 	@param Entities\Clan
	 *	@return array of Entities\Unit
	 */
	public function getClanUnits (Entities\Clan $owner)
	{
		return $this->findByOwner($owner->id);
	}

	/**
	 *	Return count of units of type, owned by clan
	 * 	@param Entities\Clan
	 * 	@param string
	 *	@return int
	 */
	public function getTotalUnitCount ($owner, $unitName)
	{
		$clanUnits = $this->findOneBy(array(
			'owner' => $owner->id,
			'type' => $unitName,
			'move' => NULL
		));

		return $clanUnits === null ? 0 : $clanUnits->count;
	}
}
