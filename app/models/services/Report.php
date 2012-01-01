<?php
namespace Services;
use Nette;
use Nette\Diagnostics\Debugger;
/**
 * Report service class
 * @author Petr Bělohlávek
 */
class Report extends BaseService
{
	public function markRead ($clan, $reportId)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update($this->entityClass, 'r')
			->set('r.read', '?1')
			->where($qb->expr()->andX(
				$qb->expr()->eq('r.owner', '?2'),
				$qb->expr()->eq('r.id', '?3')
			));
		$qb->setParameter(1, TRUE);
		$qb->setParameter(2, $clan->id);
		$qb->setParameter(3, $reportId);
		$qb->getQuery()->execute();
	}

	public function markReadAll ($clan)
	{
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update($this->entityClass, 'r')
			->set('r.read', '?1')
			->where($qb->expr()->eq('r.owner', '?2'));
		$qb->setParameter(1, TRUE);
		$qb->setParameter(2, $clan->id);
		$qb->getQuery()->execute();
	}
}
