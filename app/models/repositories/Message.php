<?php
namespace Repositories;
use Entities;
use Doctrine;
use RuleViolationException;
/**
 * Message repository class
 * @author Petr Bělohlávek
 */
class Message extends BaseRepository
{
	/**
	 * Returns received messages which are not marked as deleted
	 * @param int
	 * @return array of Entities\Message
	 */
	public function getReceivedMessages ($userId)
	{
		$qb = $this->createQueryBuilder('m');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('m.recipient', $userId),
			$qb->expr()->eq('m.deletedByRecipient', '?1')
		));
		$qb->orderBy('m.sentTime', 'DESC');
		$qb->setParameter(1, false);

		$messages = $qb->getQuery()->getResult();
		return $messages;

	}

	/**
	 * Returns sent messages which are not marked as deleted
	 * @param int
	 * @return array of Entities\Message
	 */
	public function getSentMessages ($userId)
	{
		$qb = $this->createQueryBuilder('m');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('m.sender', $userId),
			$qb->expr()->eq('m.deletedBySender', '?1')
		));
		$qb->setParameter(1, false);

		$messages = $qb->getQuery()->getResult();
		return $messages;

	}

	/**
	 * Returns number of unread messages
	 * @param int
	 * @return int
	 */
	public function getUnreadMsgCount ($userId)
	{
		$qb = $this->createQueryBuilder('m');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('m.recipient', $userId),
			$qb->expr()->eq('m.read', '?1')
		));
		$qb->setParameter(1, false);

		$messages = $qb->getQuery()->getResult();
		return count($messages);

	}

	/**
	 * Checks permissions
	 * @param Entities\User
	 * @param Entities\Message
	 * @return void
	 */
	public function checkPermission ($user, $message)
	{
		if (!($message !== null && ($message->recipient == $user || $message->sender == $user))){
			throw new RuleViolationException();
		}
	}



}
