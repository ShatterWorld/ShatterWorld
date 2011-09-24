<?php
namespace Entities;

/**
 * A building/research/training progress representation
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class Construction extends Event
{
	/**
	 * @ManyToOne(targetEntity = "Entities\Field")
	 * @var Entities\Field
	 */
	private $target;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $construction;
	
	/**
	 * @Column(type = "integer", nullable = true)
	 * @var int
	 */
	private $level;
	
	/**
	 * Constructor
	 * @param string
	 * @param Entities\Clan
	 * @param Entities\Field
	 * @param string
	 * @param int
	 */
	public function __construct ($type, Clan $owner, Field $target, $construction, $level)
	{
		parent::__construct($type, $owner);
		$this->target = $target;
		$this->construction = $construction;
		$this->level = $level;
	}
	
	/**
	 * Target getter
	 * @return Entities\Field
	 */
	public function getTarget ()
	{
		return $this->target;
	}
	
	/**
	 * Construction getter
	 * @return string
	 */
	public function getConstruction ()
	{
		return $this->construction;
	}
	
	/**
	 * Level getter
	 * @return int
	 */
	public function getLevel ()
	{
		return $this->level;
	}
}