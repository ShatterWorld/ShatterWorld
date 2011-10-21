<?php
namespace Entities;

/**
 * A military unit
 * @Entity(repositoryClass = "Repositories\Unit")
 * @Table(indexes = {@Index(name = "idx_location", columns = {"location_id"})})
 * @author Jan "Teyras" Buchar
 */
class Unit extends BaseEntity
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
	private $count;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @var Entities\Clan
	 */
	private $owner;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Field")
	 * @var Entities\Field
	 */
	private $location;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Move", inversedBy = "units")
	 * @var Entities\Move
	 */
	private $move;
	
	/**
	 * Constructor
	 * @param string
	 * @param Entities\Clan
	 */
	public function __construct ($type, Clan $owner)
	{
		$this->type = $type;
		$this->owner = $owner;
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
	 * Count getter
	 * @return int
	 */
	public function getCount ()
	{
		return $this->count;
	}
	
	/**
	 * Count setter
	 * @param integer
	 * @return void
	 */
	public function setCount ($count)
	{
		$this->count = $count;
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
	 * Location getter
	 * @return Entities\Field
	 */
	public function getLocation () 
	{
		return $this->location;
	}
	
	/**
	 * Location setter
	 * @param Entities\Field
	 * @return void
	 */
	public function setLocation (Field $location)
	{
		$this->location = $location;
	}
	
	/**
	 * Move getter
	 * @return Entities\Move
	 */
	public function getMove ()
	{
		return $this->move;
	}
	
	/**
	 * Move setter
	 * @param Entities\Move
	 * @return void
	 */
	public function setMove (Move $move = NULL)
	{
		$this->move = $move;
	}
}