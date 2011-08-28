<?php
namespace Entities;

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
	public function __construct (Clan $clan, Field $target, Event $event)
	{
		$this->clan = $clan;
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