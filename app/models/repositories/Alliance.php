<?php
namespace Repositories;
use Entities;

class Alliance extends BaseRepository
{
	public function getVisibleAlliances (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('a.id')
			->from('Entities\Field', 'f')
			->innerJoin('f.owner', 'o')
			->innerJoin('o.alliance', 'a')
			->where($qb->expr()->in('f.id', $this->context->model->getFieldRepository()->getVisibleFieldsIds($clan)))
			->groupBy('a.id');
		$alliances = array_map(function ($value) {return $value['id'];}, $qb->getQuery()->getResult());
		if ($alliances) {
			$qb = $this->createQueryBuilder('a');
			$qb->where($qb->expr()->in('a.id', $alliances));
			$qb->orderBy('a.name');
			return $qb->getQuery()->getResult();
		} else {
			return array();
		}
	}


}
