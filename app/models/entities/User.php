<?php
namespace Entities;

/**
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class User extends BaseEntity {
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $name;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $nickname;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $email;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $password;
	
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
	 * Nickname getter
	 * @return string
	 */
	public function getNickname ()
	{
		return $this->nickname;
	}
	
	/**
	 * Nickname setter
	 * @param string
	 * @return void
	 */
	public function setNickname ($nickname)
	{
		$this->nickname = $nickname;
	}
	
	/**
	 * E-mail getter
	 * @return string
	 */
	public function getEmail ()
	{
		return $this->email;
	}
	
	/**
	 * E-mail setter
	 * @param string
	 * @return void
	 */
	public function setEmail ($email)
	{
		$this->email = $email;
	}
	
	
	/**
	 * Password getter
	 * @return string
	 */
	public function getPassword ()
	{
		return $this->password;
	}
	
	/**
	 * Password setter
	 * @param string
	 * @return void
	 */
	public function setPassword ($password)
	{
		$this->password = $password;
	}
}