<?php
namespace Entities;

/**
 * A unit movement representation
 * @Entity(repositoryClass = "Repositories\Move")
 * @author Jan "Teyras" Buchar
 */
class Move extends BaseEntity
{
	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @var Entities\Clan
	 */
	private $clan;
	
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
	 * @OneToOne(targetEntity = "Entities\Event")
	 * @var Entities\Event
	 */
	private $event;
	
	/**
	 * Constructor
	 * @param Entities\Clan
	 * @param Entities\Field
	 * @param Entities\Event
	 */
	public function __construct (Clan $clan, Field $origin, Field $target, Event $event)
	{
		$this->clan = $clan;
		$this->origin = $origin;
		$this->target = $target;
		$this->event = $event;
	}
	
	/**
	 * Clan getter
	 * @return Entities\Clan
	 */
	public function getClan ()
	{
		return $this->clan;
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
	
	/**
	 * Event getter
	 * @return Entities\Event
	 */
	public function getEvent ()
	{
		return $this->event;
	}
}