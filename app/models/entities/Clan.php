<?php
namespace Entities;

/**
 * A clan entity
 * @author Jan "Teyras" Buchar
 * @Entity
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
	public function setUser (Entities\User $User)
	{
		$this->user = $user;
	}
	
}