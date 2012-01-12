<?php
namespace Entities;

/**
 * An event notification
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class Notification extends Event
{
	/**
	 * @ManyToOne(targetEntity = "Entities\Event")
	 * @var Entities\Event
	 */
	private $subject;
	
	/**
	 * Subject getter
	 * @return Entites\Event
	 */
	public function getSubject ()
	{
		return $this->subject;
	}
	
	/**
	 * Subject setter
	 * @param Entities\Event 
	 * @return void
	 */
	public function setSubject (Event $subject)
	{
		$this->subject = $subject;
	}
}