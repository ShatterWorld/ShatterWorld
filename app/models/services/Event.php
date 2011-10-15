<?php
namespace Services;
use RuleViolationException;

class Event extends BaseService
{
	const LOCK_TIMEOUT = 30;
	
	public function processPendingEvents ()
	{
		$userId = $this->context->user->id;
		$now = new \DateTime('@' . date('U'));
		$timeout = microtime(TRUE) + self::LOCK_TIMEOUT;
		$qb = $this->entityManager->createQueryBuilder();
		$qb->update('Entities\Event', 'e')
			->set('e.lockUserId', '?1')
			->set('e.lockTimeout', '?2')
			->where(
				$qb->expr()->andX(
					$qb->expr()->lte('e.term', '?3'),
					$qb->expr()->orX(
						$qb->expr()->lt('e.lockTimeout', '?4'),
						$qb->expr()->isNull('e.lockUserId')
					),
					$qb->expr()->eq('e.processed', '?5')
				)
			);
		$qb->setParameter(1, $userId);
		$qb->setParameter(2, $timeout);
		$qb->setParameter(3, $now);
		$qb->setParameter(4, $now->format('U'));
		$qb->setParameter(5, FALSE);
		if ($qb->getQuery()->execute() !== 0) {
			foreach ($this->getRepository()->findPendingEvents($userId, $timeout) as $event) {
				$eventRule = $this->context->rules->get('event', $event->type);
				if ($eventRule->isValid($event)) {
					$report = $eventRule->process($event);
					$this->context->model->getReportService()->create(array(
						'event' => $event,
						'data' => $report
					));
				} else {
					$this->update($event, array('failed' => TRUE), FALSE);
				}
				$this->update($event, array('processed' => TRUE), FALSE);
			}
		}
		$this->entityManager->flush();
	}
	
	public function create ($values, $flush = TRUE)
	{
		$event = parent::create($values, FALSE);
		$this->entityManager->detach($event);
		if ($this->context->rules->get('event', $event->type)->isValid($event)) {
			$this->entityManager->persist($event);
			if ($flush) {
				$this->entityManager->flush();
			}
		} else {
			throw new RuleViolationException;
		}
		return $event;
	}
}