<?php
namespace Entities;

/**
 * An entity representing a finished or progressing quest
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class Quest extends BaseEntity
{
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $type;
	
	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $completed;
	
	/**
	 * @Column(type = "datetime")
	 * @var DateTime
	 */
	private $start;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @var Entities\Clan
	 */
	private $owner;
	
	/**
	 * Constructor
	 * @param Entities\Clan
	 * @param string
	 * @param DateTime
	 */
	public function __construct (Clan $owner, $type, $start = NULL)
	{
		$this->owner = $owner;
		$this->type = $type;
		if (!$start) {
			$start = new \DateTime();
		}
		$this->start = $start;
		$this->completed = FALSE;
	}
	
	/**
	 * Type getter
	 * @return string
	 */
	public function getType ()
	{
		return $this->type;
	}
	
	/**
	 * Is the quest completed?
	 * @return bool
	 */
	public function isCompleted ()
	{
		return $this->completed;
	}
	
	/**
	 * Completion setter
	 * @param bool
	 * @return void
	 */
	public function setCompleted ($completed)
	{
		$this->completed = $completed;
	}
	
	/**
	 * Start getter
	 * @return DateTime
	 */
	public function getStart ()
	{
		return $this->start;
	}
	
	/**
	 * Owner getter
	 * @return Entities\Clan
	 */
	public function getOwner ()
	{
		return $this->owner;
	}
}