<?php
namespace Entities;

/**
 * A pending event entity
 * @Entity(repositoryClass = "Repositories\Event")
 * @Table(indexes = {@Index(name = "idx_lock", columns = {"term", "processed", "lockUserId", "lockTimeout"})})
 * @InheritanceType("SINGLE_TABLE")
 * @DicriminatorColumn(name = "class", type = "string")
 * @DiscriminatorMap({"Move" = "Move", "Construction" = "Construction", "Shipment" = "Shipment"})
 * @author Jan "Teyras" Buchar
 */
abstract class Event extends BaseEntity {
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $type;
	
	/**
	 * @Column(type = "datetime")
	 * @var DateTime
	 */
	private $term;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @JoinColumn(onDelete = "CASCADE")
	 * @var Entities\Clan
	 */
	private $owner;
	
	/**
	 * @Column(type = "integer", nullable = true)
	 * @var int
	 */
	private $lockUserId;
	
	/**
	 * @Column(type = "float", nullable = true)
	 * @var float
	 */
	private $lockTimeout;
	
	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $processed;
	
	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $failed;
	
	/**
	 * Constructor
	 * @param string
	 * @param Entities\Clan
	 */
	public function __construct ($type, Clan $owner)
	{
		$this->type = $type;
		$this->owner = $owner;
		$this->processed = FALSE;
		$this->failed = FALSE;
	}
	
	/**
	 * Get clans that are relevant to this event
	 * @return array of Entities\Clan
	 */
	public function getObservers ()
	{
		return array($this->owner);
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
	 * Term getter
	 * @return DateTime
	 */
	public function getTerm ()
	{
		return $this->term;
	}
	
	/**
	 * Get time left to the term of the event
	 * @return int
	 */
	public function getRemainingTime ()
	{
		return max($this->term->format('U') - date('U'), 0);
	}
	
	/**
	 * Term setter
	 * @param DateTime
	 * @return void
	 */
	public function setTerm ($term)
	{
		$this->term = $term;
	}
	
	/**
	 * Timeout setter
	 * @param int
	 * @param DateTime
	 * @return void
	 */
	public function setTimeout ($timeout, $time = NULL)
	{
		$this->term = $time ? new \DateTime('@' . (intval($time->format('U')) + $timeout)) : new \DateTime('@' . (date('U') + $timeout));
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
	 * Has the event already been processed?
	 * @return bool
	 */
	public function isProcessed ()
	{
		return $this->processed;
	}
	
	/**
	 * Processed setter
	 * @param bool
	 * @return void
	 */
	public function setProcessed ($processed = TRUE)
	{
		$this->processed = $processed;
	}
	
	/**
	 * Has the event failed?
	 * @return bool
	 */
	public function isFailed ()
	{
		return $this->failed;
	}
	
	/**
	 * Dailed setter
	 * @param bool
	 * @return void
	 */
	public function setFailed ($failed = TRUE)
	{
		$this->failed = $failed;
	}
}