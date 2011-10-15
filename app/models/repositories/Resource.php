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
		foreach ($qb->getQuery()->useResultCache(TRUE)->getResult() as $resource) {
			$result[$resource->type] = $resource;
		}
		return $result;
	}
	
	public function getResourcesArray (Entities\Clan $clan)
	{
		$qb = $this->createQueryBuilder('r');
		$qb->where($qb->expr()->eq('r.clan', $clan->id));
		$result = array();
		foreach ($qb->getQuery()->getResult() as $resource) {
			$row = array();
			$resource->settleBalance();
			$row['balance'] = $resource->balance;
			$row['production'] = $resource->production;
			$row['storage'] = $resource->storage;
			$result[$resource->type] = $row;
		}
		return $result;
	}
	
	public function checkResources (Entities\Clan $clan, $price)
	{
		$resources = $this->findResourcesByClan($clan);
		foreach ($price as $resource => $cost) {
			if (!$resources[$resource]->has($cost)) {
				return FALSE;
			}
		}
		return TRUE;
	}
}