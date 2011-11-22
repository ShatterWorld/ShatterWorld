<?php
namespace Entities;

/**
 * An application user entity
 * @Entity(repositoryClass = "Repositories\User")
 * @author Jan "Teyras" Buchar
 */
class User extends BaseEntity {

	/**
	 * @Column(type = "string", unique = true)
	 * @var string
	 */
	private $login;

	/**
	 * @Column(type = "string", unique = true)
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
	 * @OneToOne(targetEntity = "Entities\Clan")
	 * @var Entities\Clan
	 */
	private $activeClan;

	/**
	 * Calculate a salted hash of the password
	 * @param string
	 * @param string
	 * @return string
	 */
	protected function calculatePasswordHash ($login, $password)
	{
		return sha1($login . $password);
	}

	/**
	 * Verify given password against this entity
	 * @param string
	 * @return bool
	 */
	public function verifyPassword ($password)
	{
		return $this->calculatePasswordHash($this->login, $password) === $this->password;
	}

	/**
	 * Login getter
	 * @return string
	 */
	public function getLogin ()
	{
		return $this->login;
	}

	/**
	 * Login setter
	 * @param string
	 * @return void
	 */
	public function setLogin ($login)
	{
		$this->login = $login;
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
		$this->password = $this->calculatePasswordHash($this->login, $password);
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
	
	/**
	 * Active clan getter
	 * @return Entities\Clan
	 */
	public function getActiveClan ()
	{
		return $this->activeClan;
	}
	
	/**
	 * Active clan setter
	 * @param Entities\Clan
	 * @return void
	 */
	public function setActiveClan (Clan $activeClan)
	{
		$this->activeClan = $activeClan;
	}
}
