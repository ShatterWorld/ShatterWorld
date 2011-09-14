<?php
namespace Entities;

/**
 * A building/research/training progress representation
 * @Entity(repositoryClass = "Repositories\Construction")
 * @author Jan "Teyras" Buchar
 */
class Construction extends BaseEntity
{
	/**
	 * @OneToOne(targetEntity = "Entities\Field")
	 * @var Entities\Field
	 */
	private $field;
	
	/**
	 * @OneToOne(targetEntity = "Entities\Event")
	 * @var Entities\Event
	 */
	private $event;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $type;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $constructionType;
	
	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $level;
	
	/**
	 * Constructor
	 * @param Entities\Field
	 * @param Entities\Event
	 * @param string
	 * @param string
	 * @param int
	 */
	public function __construct (Field $field, Event $event, $type, $constructionType, $level)
	{
		$this->field = $field;
		$this->event = $event;
		$this->type = $type;
		$this->constructionType = $constructionType;
		$this->level = $level;
	}
	
	/**
	 * Field getter
	 * @return Entities\Field
	 */
	public function getField ()
	{
		return $this->field;
	}
	
	
	/**
	 * Event getter
	 * @return Entities\Event
	 */
	public function getEvent ()
	{
		return $this->event;
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
	 * Construction type getter
	 * @return string
	 */
	public function getConstructionType ()
	{
		return $this->constructionType;
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