<?php
namespace Repositories;
use Entities;
use Nette\Diagnostics\Debugger;

class Quest extends BaseRepository
{
	public function findActive (Entities\Clan $clan)
	{
		$qb = $this->createQueryBuilder('q');
		$qb->where(
			$qb->expr()->andX(
				$qb->expr()->eq('q.owner', '?0'),
				$qb->expr()->eq('q.completed', '?1'),
				$qb->expr()->eq('q.failed', '?2')
			)
		);
		$qb->setParameters(array(
			$clan->id,
			FALSE,
			FALSE
		));
		$query = $qb->getQuery();
// 		$query->useResultCache(TRUE);
		return $query->getResult();
	}

	public function findCompleted (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('q.type', 'max(q.level) as level')
			->from('Entities\Quest', 'q')
			->where($qb->expr()->andX(
				$qb->expr()->eq('q.owner', $clan->id),
				$qb->expr()->eq('q.completed', '?1'),
				$qb->expr()->eq('q.failed', '?2')
			))
			->groupBy('q.type')
			->addGroupBy('q.owner');
		$qb->setParameter(1, true);
		$qb->setParameter(2, false);

		return $qb->getQuery()->getResult();
	}
}
