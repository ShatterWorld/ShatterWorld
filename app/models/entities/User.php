<?php
namespace Entities;
use Doctrine;

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
	 * @OneToMany(targetEntity = "Entities\Clan", mappedBy = "user")
	 * @var Doctrine\Common\Collections\ArrayCollection
	 */
	private $clans;
	
	/**
	 * @OneToOne(targetEntity = "Entities\Clan")
	 * @JoinColumn(onDelete = "SET NULL")
	 * @var Entities\Clan
	 */
	private $activeClan;

	/**
	 * Constructor
	 */
	public function __construct ()
	{
		$this->clans = new Doctrine\Common\Collections\ArrayCollection();
	}
	
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
	 * Clans getter
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getClans ()
	{
		return $this->clans;
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
