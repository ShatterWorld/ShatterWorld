<?php
namespace Entities;

/**
 * A message
 * @Entity(repositoryClass = "Repositories\Message")
 * @author Petr Bělohlávek
 */
class Message extends BaseEntity
{
	/**
	 * @Column(type = "datetime")
	 * @var DateTime
	 */
	private $sentTime;

	/**
	 * @ManyToOne(targetEntity = "Entities\User")
	 * @var Entities\User
	 */
	private $sender;

	/**
	 * @ManyToOne(targetEntity = "Entities\User")
	 * @var Entities\User
	 */
	private $recipient;

	/**
	 * @Column(type = "boolean", name = "isRead")
	 * @var bool
	 */
	private $read;

	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $deletedBySender;

	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $deletedByRecipient;

	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $subject;

	/**
	 * @Column(type = "text")
	 * @var string
	 */
	private $body;

	/**
	 * Constructor
	 * @param Entities\Event
	 * @param array|object
	 */
	public function __construct ($sentTime)
	{
		$this->read = FALSE;
		$this->deletedByRecipient = FALSE;
		$this->deletedBySender = FALSE;
		$this->sentTime = $sentTime;
	}

	/**
	 * Sender getter
	 * @return Entities\User
	 */
	public function getSender ()
	{
		return $this->sender;
	}

	/**
	 * Sender setter
	 * @param Entities\User
	 * @return void
	 */
	public function setSender ($sender)
	{
		$this->sender = $sender;
	}

	/**
	 * Recipient getter
	 * @return Entities\User
	 */
	public function getRecipient ()
	{
		return $this->recipient;
	}

	/**
	 * Recipient setter
	 * @param Entities\User
	 * @return void
	 */
	public function setRecipient ($recipient)
	{
		$this->recipient = $recipient;
	}

	/**
	 * Has the message been read?
	 * @return bool
	 */
	public function isRead ()
	{
		return $this->read;
	}

	/**
	 * Mark the message as read/unread
	 * @param bool
	 * @return void
	 */
	public function setRead ($read = TRUE)
	{
		$this->read = $read;
	}

	/**
	 * Has the message been deleted by sender?
	 * @return bool
	 */
	public function isDeletedBySender ()
	{
		return $this->deletedBySender;
	}

	/**
	 * Mark the message as deleted by sender
	 * @param bool
	 * @return void
	 */
	public function setDeletedBySender ($deletedBySender = TRUE)
	{
		$this->deletedBySender = $deletedBySender;
	}

	/**
	 * Has the message been deleted by recipient?
	 * @return bool
	 */
	public function isDeletedByRecipient ()
	{
		return $this->deletedByRecipient;
	}

	/**
	 * Mark the message as deleted by recipient
	 * @param bool
	 * @return void
	 */
	public function setDeletedByRecipient ($deletedByRecipient = TRUE)
	{
		$this->deletedByRecipient = $deletedByRecipient;
	}

	/**
	 * Subject getter
	 * @return string
	 */
	public function getSubject ()
	{
		return $this->subject;
	}

	/**
	 * Subject setter
	 * @param string
	 * @return void
	 */
	public function setSubject ($subject)
	{
		$this->subject = $subject;
	}

	/**
	 * Body getter
	 * @return string
	 */
	public function getBody ()
	{
		return $this->body;
	}

	/**
	 * Body setter
	 * @param string
	 * @return void
	 */
	public function setBody ($body)
	{
		$this->body = $body;
	}

}
