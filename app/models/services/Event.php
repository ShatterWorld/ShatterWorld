<?php
namespace Services;
use Entities;
use RuleViolationException;

class Event extends BaseService
{
	const LOCK_TIMEOUT = 30;

	/**
	 * A queue of events to be processed
	 * @var array
	 */
	protected $eventQueue;

	/**
	 * Event processing time
	 * @var DateTime
	 */
	protected $eventTime;

	public function processPendingEvents ()
	{
		$userId = $this->context->user->id;
		$this->eventTime = $now = new \DateTime('@' . date('U'));
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
			$this->eventQueue = $this->getRepository()->findPendingEvents($userId, $timeout);
			foreach ($this->eventQueue as $event) {
				$eventRule = $this->context->rules->get('event', $event->type);
				if ($eventRule->isValid($event)) {
					$report = $eventRule->process($event, $this);
					$this->context->model->getReportService()->create(array(
						'owner' => $event->owner,
						'event' => $event,
						'data' => $report
					), FALSE);
					if ($event->target && $event->target->owner && $event->target->owner->id !== $event->owner->id){
						$this->context->model->getReportService()->create(array(
							'owner' => $event->target->owner,
							'event' => $event,
							'data' => $report
						), FALSE);
					}
				} else {
					$this->update($event, array('failed' => TRUE), FALSE);
				}
				$this->update($event, array('processed' => TRUE), FALSE);
			}
		}

		if($clan = $this->context->model->clanRepository->getPlayerClan()){
			$this->context->model->questService->processCompletion($clan, $now);
		}
		$this->entityManager->flush();
	}

	/**
	 * Add an event to the event processing queue if it is to be processed
	 * @param Entities\Event
	 * @return void
	 */
	public function queueEvent (Entities\Event $event)
	{
		if ($event->term < $this->eventTime) {
			foreach ($this->eventQueue as $offset => $queuedEvent) {
				if ($event->term < $queuedEvent->term) break;
			}
			$precessors = array_slice($this->eventQueue, 0, $offset - 1);
			$successors = array_slice($this->eventQueue, $offset);
			$precessors[] = $event;
			$this->eventQueue = array_merge($precessors, $successors);
		}
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
