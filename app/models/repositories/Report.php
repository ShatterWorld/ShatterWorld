<?php
namespace Repositories;
use Entities;
use Nette\Diagnostics\Debugger;
use Nette\Utils\Json;

class Report extends BaseRepository
{
	public function findByOwner (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('r', 'e')->from($this->getEntityName(), 'r')->innerJoin('r.event', 'e');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('r.owner', '?1')
		));
		$qb->orderBy('e.term', 'DESC');
		$qb->setParameter(1, $clan->id);
		return $qb->getQuery()->getResult();
	}

	public function getPage (Entities\Clan $clan, $pageLength, $page = 1)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('r', 'e')->from($this->getEntityName(), 'r')->innerJoin('r.event', 'e');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('r.owner', '?1')
		));
		$qb->orderBy('e.term', 'DESC');
		$qb->setFirstResult(($page - 1) * $pageLength);
		$qb->setMaxResults($pageLength);
		$qb->setParameter(1, $clan->id);
		return $qb->getQuery()->getResult();
	}

	public function getCount (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select($qb->expr()->count('r.id'))->from($this->getEntityName(), 'r');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('r.owner', '?1')
		));
		$qb->setParameter(1, $clan->id);
		return $qb->getQuery()->getSingleScalarResult();
	}

	public function countUnread (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select($qb->expr()->count('r.id'))->from($this->getEntityName(), 'r');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('r.owner', '?1'),
			$qb->expr()->eq('r.read', '?2')
		));
		$qb->setParameter(1, $clan->id);
		$qb->setParameter(2, FALSE);
		return $qb->getQuery()->getSingleScalarResult();
	}

	public function getTotalVictoryCount (Entities\Clan $clan)
	{
		$now = new \DateTime('@' . strtotime('-1 day', date('U')));

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('r.data, e.term')
			->from($this->getEntityName(), 'r')
			->leftJoin('r.event', 'e')
			->where($qb->expr()->andX(
				$qb->expr()->eq('r.owner', $clan->id),
				$qb->expr()->lte('?1', 'e.term')
			));

		$qb->setParameter(1, $now);


		$reports =  $qb->getQuery()->getResult();

		$count = 0;
		foreach($reports as $report){
			$data = Json::decode($report['data']);
			if (isset($data->successful) && $data->successful){
				$count++;
			}
		}

		return $count;
	}








}
