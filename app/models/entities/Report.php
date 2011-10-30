<?php
namespace Entities;
use Nette\Utils\Json;

/**
 * A report of an event
 * @Entity(repositoryClass = "Repositories\Report")
 * @author Jan "Teyras" Buchar
 */
class Report extends BaseEntity
{
	/**
	 * @ManyToOne(targetEntity = "Entities\Event")
	 * @JoinColumn(onDelete = "CASCADE")
	 * @var Entities\Event
	 */
	private $event;

	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $data;

	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $isRead;

	/**
	 * Constructor
	 * @param Entities\Event
	 * @param array|object
	 */
	public function __construct (Event $event, $data = array())
	{
		$this->event = $event;
		$this->data = Json::encode($data);
		$this->isRead = FALSE;
	}

	/**
	 * Event getter
	 * @return Entities\Event
	 */
	public function getEvent ()
	{
		return $this->event;
	}

	/**
	 * Data getter
	 * @return object
	 */
	public function getData ()
	{
		return Json::decode($this->data, Json::FORCE_ARRAY);
	}

	/**
	 * Has the report been read?
	 * @return bool
	 */
	public function getIsRead ()
	{
		return $this->isRead;
	}

	/**
	 * Mark the report as read/unread
	 * @param bool
	 * @return void
	 */
	public function setIsRead ($read = TRUE)
	{
		$this->isRead = $read;
	}
}
