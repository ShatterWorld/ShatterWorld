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
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @JoinColumn(onDelete = "CASCADE")
	 * @var Entities\Clan
	 */
	private $owner;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $data;

	/**
	 * @Column(type = "boolean", name = "isRead")
	 * @var bool
	 */
	private $read;

	/**
	 * Constructor
	 * @param Entities\Event
	 * @param array|object
	 */
	public function __construct (Event $event, $data = array())
	{
		$this->event = $event;
		$this->owner = $event->owner;
		$this->data = Json::encode($data);
		$this->read = FALSE;
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
	 * Owner getter
	 * @return Entities\Clan
	 */
	public function getOwner ()
	{
		return $this->owner;
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
	public function isRead ()
	{
		return $this->read;
	}

	/**
	 * Mark the report as read/unread
	 * @param bool
	 * @return void
	 */
	public function setRead ($read = TRUE)
	{
		$this->read = $read;
	}
}
