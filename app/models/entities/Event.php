<?php
namespace Entities;

/**
 * A pending event entity
 * @Entity(repositoryClass = "Repositories\Event")
 * @author Jan "Teyras" Buchar
 */
class Event extends BaseEntity {
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $type;
	
	/**
	 * @Column(type = "datetime")
	 * @var DateTime
	 */
	private $term;
	
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
	 * Term getter
	 * @return DateTime
	 */
	public function getTerm ()
	{
		return $this->type;
	}
	
	/**
	 * Term setter
	 * @param DateTime
	 * @return void
	 */
	public function setTerm ($term)
	{
		$this->term = $term;
	}
}