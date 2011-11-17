<?php
namespace Services;
use Nette\Diagnostics\Debugger;
use Nette\DateTime;
use Entities;
use RuleViolationException;

/**
 * Message service class
 * @author Petr Bělohlávek
 */
class Message extends BaseService
{
	/**
	 * Creates an object as the parent does
	 * @param array
	 * @param bool
	 * @return Entities\Message
	 */
	public function create ($values, $flush = TRUE)
	{
		$values['sentTime'] = new DateTime();
		$message = parent::create($values, $flush);
		return $message;
	}

	/**
	 * Marks the given message (un)read
	 * @param Entities\User
	 * @param Entities\Message
	 * @param bool
	 * @return void
	 */
	public function markRead ($user, $message, $value = TRUE)
	{
		if($message !== null && $message->recipient == $user){
			$this->update($message, array('read' => $value));
		}
		else{
			throw new RuleViolationException();
		}
	}

	/**
	 * Deletes the message by recipient
	 * @param Entities\User
	 * @param Entities\Message
	 * @return void
	 */
	public function deleteByRecipient ($user, $message)
	{
		if($message !== null && $message->recipient == $user){
			$this->update($message, array('deletedByRecipient' => true));
		}
		else{
			throw new RuleViolationException();
		}
	}

	/**
	 * Deletes the message by sender
	 * @param Entities\User
	 * @param Entities\Message
	 * @return void
	 */
	public function deleteBySender ($user, $message)
	{
		if($message !== null && $message->sender == $user){
			$this->update($message, array('deletedBySender' => true));
		}
		else{
			throw new RuleViolationException();
		}
	}

}
