<?php
namespace Repositories;
use Entities;

class Resource extends BaseRepository
{
	public function findResourcesByClan (Entities\Clan $clan)
	{
		$qb = $this->createQueryBuilder('r');
		$qb->where($qb->expr()->eq('r.clan', $clan->id));
		$result = array();
		foreach ($qb->getQuery->getResult() as $resource) {
			$result[$resource->type] = $resource;
		}
		return $result;
	}
	
	public function getResourcesArray (Entities\Clan $clan)
	{
		$qb = $this->createQueryBuilder('r');
		$qb->where($qb->expr()->eq('r.clan', $clan->id));
		$result = array();
		foreach ($qb->getQuery->getArrayResult() as $resource) {
			$result[$resource['type']] = $resource;
		}
		return $result;
	}
}