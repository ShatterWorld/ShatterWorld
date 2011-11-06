<?php
namespace Entities;

/**
 * A research entity
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class Research extends BaseEntity
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
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @JoinColumn(onDelete = "CASCADE")
	 * @var Entities\Clan
	 */
	private $owner;
	
	/**
	 * Constructor
	 * @param Entities\Clan
	 * @param string
	 */
	public function __construct ($owner, $type)
	{
		$this->owner = $owner;
		$this->type = $type;
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
	 * Owner getter
	 * @return Entities\Clan
	 */
	public function getOwner ()
	{
		return $this->owner;
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
}