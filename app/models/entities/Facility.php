<?php
namespace Entities;

/**
 * A facility
 * @Entity(repositoryClass = "Repositories\Facility")
 * @author Jan "Teyras" Buchar
 */
class Facility extends BaseEntity
{
	/**
	 * @OneToOne(targetEntity = "Entities\Field", mappedBy = "facility")
	 * @var Entities\Field
	 */
	private $location;
	
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
	private $damaged;
	
	/**
	 * Constructor
	 * @param Entities\Field
	 * @param string
	 */
	public function __construct (Field $location, $type)
	{
		$this->setLocation($location);
		$this->type = $type;
		$this->level = 1;
		$this->damaged = FALSE;
	}
	
	/**
	 * Location getter
	 * @return Entities\Field
	 */
	public function getLocation ()
	{
		return $this->location;
	}
	
	public function setLocation (Field $location)
	{
		$this->location = $location;
		$location->setFacility($this);
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
	 * Level setter
	 * @param int
	 * @return void
	 */
	public function setLevel ($level)
	{
		$this->level = $level;
	}
	
	/**
	 * Is the facility damaged?
	 * @return bool
	 */
	public function isDamaged ()
	{
		return $this->damaged;
	}
	
	/**
	 * Damaged setter
	 * @param bool
	 * @return void
	 */
	public function setDamaged ($damaged)
	{
		$this->damaged = $damaged;
	}
}