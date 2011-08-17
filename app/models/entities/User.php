<?php
namespace Entities;

/**
 * An application user entity
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class User extends BaseEntity {
	
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
	 * @Column(type = "string")
	 * @var string
	 */
	private $role;
	
	/**
	 * Calculate a salted hash of the password
	 * @param string
	 * @param string
	 * @return string
	 */
	protected function calculatePasswordHash ($nickname, $password)
	{
		return sha1($nickname . $password);
	}
	
	/**
	 * Verify given password against this entity
	 * @param string
	 * @return bool
	 */
	public function verifyPassword ($password)
	{
		return $this->calculatePasswordHash($this->nickname, $password) === $this->password;
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
	 * Password setter
	 * @param string
	 * @return void
	 */
	public function setPassword ($password)
	{
		$this->password = $this->calculatePasswordHash($this->nickname, $password);
	}
	
	/**
	 * Role getter
	 * @return string
	 */
	public function getRole ()
	{
		return $this->role;
	}
	
	/**
	 * Role setter
	 * @param string
	 * @return void
	 */
	public function setRole ($role)
	{
		$this->role = $role;
	}
}