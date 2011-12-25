<?php
namespace Repositories;
use Entities;
use Doctrine\Doctrine_Query;

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
		/*
		SELECT q.type, q.owner_id, max(q.level) as maxlevel
		FROM Quest q
		where q.failed=0 and q.completed=1
		group by q.type, q.owner_id
		*/


		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('q.owner, q.type, max(q.level) as maxlevel')
			->from('Entities\Quest', 'q')
			->where($qb->expr()->andX(
				$qb->expr()->eq('q.owner', $clan->id),
				$qb->expr()->eq('q.completed', '?1'),
				$qb->expr()->eq('q.failed', '?2')
			))
			->groupBy('q.type, q.owner');
		$qb->setParameter(1, true);
		$qb->setParameter(2, false);

		$quests = $qb->getQuery()->getResult();
		Debugger::barDump($quests);


		return $this->findBy(array(
			'owner' => $clan->id,
			'completed' => TRUE,
			'failed' => FALSE
		));
	}
}
