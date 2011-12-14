<?php
namespace Repositories;
use Entities;

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
		return $this->findBy(array(
			'owner' => $clan->id,
			'completed' => TRUE,
			'failed' => FALSE
		));
	}
}
