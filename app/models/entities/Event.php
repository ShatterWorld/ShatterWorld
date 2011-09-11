<?php
namespace Entities;

/**
 * A pending event entity
 * @Entity(repositoryClass = "Repositories\Event")
 * @Table(indexes = {@Index(name = "idx_lock", columns = {"term", "processed", "lockUserId", "lockTimeout"})})
 * @author Jan "Teyras" Buchar
 */
class Event extends BaseEntity {
	
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
	 * Constructor
	 * @param string
	 * @param Entities\Clan
	 */
	public function __construct ($type, Clan $owner)
	{
		$this->type = $type;
		$this->owner = $owner;
		$this->processed = FALSE;
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
	 * Term setter
	 * @param DateTime
	 * @return void
	 */
	public function setTerm ($term)
	{
		$this->term = $term;
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
}