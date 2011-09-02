<?php
namespace Entities;

/**
 * A pending event entity
 * @Entity(repositoryClass = "Repositories\Event")
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
	 */
	public function __construct ($type)
	{
		$this->type = $type;
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
		return $this->type;
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