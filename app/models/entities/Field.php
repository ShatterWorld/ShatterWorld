<?php
namespace Entities;

/**
 * A game plan field entity
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class Field extends BaseEntity {
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Clan", inversedBy = "fields")
	 * @var Entities\Clan
	 */
	private $owner;
	
	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $coordX;
	
	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $coordY;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $type;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $facility;
	
	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $level;
	
	/**
	 * Owner getter
	 * @return Entities\Clan
	 */
	public function getOwner ()
	{
		return $this->owner;
	}
	
	/**
	 * Owner setter
	 * @param Entities\Clan
	 * @return void
	 */
	public function setOwner (Clan $clan)
	{
		$this->owner = $clan;
		$clan->getFields()->add($clan);
	}
	
	/**
	 * Coordinates getter
	 * @return array
	 */
	public function getCoords ()
	{
		return array($this->coordX, $this->coordY);
	}
	
	/**
	 * Coordinates setter
	 * @param int
	 * @param int
	 * @return void
	 */
	public function setCoords ($x, $y)
	{
		$this->coordX = $x;
		$this->coordY = $y;
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
	 * Type setter
	 * @param string
	 * @return void
	 */
	public function setType ($type)
	{
		$this->type = $type;
	}
	
	/**
	 * Facility getter
	 * @return string
	 */
	public function getFacility ()
	{
		return $this->facility;
	}
	
	/**
	 * Facility setter
	 * @param string
	 * @return void
	 */
	public function setFacility ($facility)
	{
		$this->facility = $facility;
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