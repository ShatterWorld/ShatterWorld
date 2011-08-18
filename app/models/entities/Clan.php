<?php
namespace Entities;
use Doctrine;

/**
 * A clan entity
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class Clan extends BaseEntity {
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $name;
	
	/**
	 * @OneToOne(targetEntity = "Entities\User")
	 * @var Entities\User
	 */
	private $user;
	
	/**
	 * @OneToMany(targetEntity = "Entities\Field", mappedBy = "owner")
	 * @var Doctrine\Common\Collections\ArrayCollection
	 */
	private $fields;
	
	public function __construct ()
	{
		$this->fields = new Doctrine\Common\Collections\ArrayCollection();
	}
	
	/**
	 * Name getter
	 * @return string
	 */
	public function getName ()
	{
		return $this->name;
	}
	
	/**
	 * Name setter
	 * @param string
	 * @return void
	 */
	public function setName ($name)
	{
		$this->name = $name;
	}
	
	/**
	 * User getter
	 * @return Entities\User
	 */
	public function getUser ()
	{
		return $this->user;
	}
	
	/**
	 * User setter
	 * @param Entities\User
	 * @return void
	 */
	public function setUser (User $user)
	{
		$this->user = $user;
	}
	
	/**
	 * Fields getter
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getFields ()
	{
		return $this->fields;
	}
	
	/**
	 * Add a field to the clan's territory
	 * @param Entities\Field
	 * @return void
	 */
	public function addField (Field $field)
	{
		$field->setOwner($this);
		$this->fields->add($field);
	}
}