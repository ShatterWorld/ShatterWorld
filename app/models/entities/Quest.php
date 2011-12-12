<?php
namespace Entities;

/**
 * An entity representing a finished or progressing quest
 * @Entity(repositoryClass = "Repositories\Quest")
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
	 * @Column(type = "integer")
	 * @var int
	 */
	private $level;
	
	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $completed;
	
	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $failed;
	
	/**
	 * @Column(type = "datetime")
	 * @var DateTime
	 */
	private $start;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @JoinColumn(onDelete = "CASCADE")
	 * @var Entities\Clan
	 */
	private $owner;
	
	/**
	 * Constructor
	 * @param Entities\Clan
	 * @param string
	 * @param DateTime
	 */
	public function __construct (Clan $owner, $type, $level = 1, $start = NULL)
	{
		$this->owner = $owner;
		$this->type = $type;
		$this->level = $level;
		if (!$start) {
			$start = new \DateTime();
		}
		$this->start = $start;
		$this->completed = FALSE;
		$this->failed = FALSE;
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
	 * Level getter
	 * @return int
	 */
	public function getLevel ()
	{
		return $this->level;
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
	 * Has the player failed to complete the quest?
	 * @return bool
	 */
	public function isFailed ()
	{
		return $this->failed;
	}
	
	/**
	 * Fail setter
	 * @param bool
	 * @return void
	 */
	public function setFailed ($failed)
	{
		$this->failed = $failed;
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