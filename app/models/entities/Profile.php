<?php
namespace Entities;

/**
 * A user profile entity
 * @Entity
 * @author Petr Bělohlávek
 */
class Profile extends BaseEntity {
	
	/**
	 * @Column(type = "string", nullable = true)
	 * @var string
	 */
	private $name;
	
	/**
	 * @OneToOne(targetEntity = "Entities\User")
	 * @var Entities\User
	 */
	private $user;
	
	/**
	 * @Column(type = "integer", nullable = true)
	 * @var integer
	 */
	private $age;
	
	/**
	 * @Column(type = "string", nullable = true)
	 * @var integer
	 */
	private $gender;
	
	/**
	 * @Column(type = "string", nullable = true)
	 * @var string
	 */
	private $icq;
	
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
	 * Name getter
	 * @return int
	 */
	public function getAge ()
	{
		return $this->age;
	}
	
	/**
	 * Name setter
	 * @param int
	 * @return void
	 */
	public function setAge ($age)
	{
		$this->age = $age;
	}
	
	/**
	 * Name getter
	 * @return string
	 */
	public function getGender ()
	{
		return $this->gender;
	}
	
	/**
	 * Name setter
	 * @param string
	 * @return void
	 */
	public function setGender ($gender)
	{
		$this->gender = $gender;
	}
	
	/**
	 * Name getter
	 * @return string
	 */
	public function getIcq ()
	{
		return $this->icq;
	}
	
	/**
	 * Name setter
	 * @param string
	 * @return void
	 */
	public function setIcq ($icq)
	{
		$this->icq = $icq;
	}
	



	
}
