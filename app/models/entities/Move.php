<?php
namespace Entities;

/**
 * A unit movement representation
 * @Entity(repositoryClass = "Repositories\Move")
 * @author Jan "Teyras" Buchar
 */
class Move extends Event
{
	/**
	 * @ManyToOne(targetEntity = "Entities\Field")
	 * @var Entities\Field
	 */
	private $origin;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Field")
	 * @var Entities\Field
	 */
	private $target;
	
	/**
	 * Constructor
	 * @param string
	 * @param Entities\Clan
	 * @param Entities\Field
	 * @param Entities\Field
	 */
	public function __construct ($type, Clan $owner, Field $origin, Field $target)
	{
		parent::__construct($type, $owner);
		$this->origin = $origin;
		$this->target = $target;
	}
	
	/**
	 * Origin getter
	 * @return Entities\Field
	 */
	public function getOrigin ()
	{
		return $this->origin;
	}
	
	/**
	 * Target getter
	 * @return Entities\Field
	 */
	public function getTarget ()
	{
		return $this->target;
	}
}