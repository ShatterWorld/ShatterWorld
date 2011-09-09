<?php
namespace Entities;
use InsufficientResourcesException;

/**
 * A resource balance
 * @Entity(repositoryClass = "Repositories\Resource")
 * @Table(indexes = {@Index(name = "idx_clan", columns = {"clan_id"}), @Index(name = "idx_resource", columns = {"clan_id", "type"})})
 * @author Jan "Teyras" Buchar
 */
class Resource extends BaseEntity
{
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $type;
	
	/**
	 * @Column(type = "float")
	 * @var int
	 */
	private $balance;
	
	/**
	 * @Column(type = "datetime")
	 * @var DateTime
	 */
	private $clearance;
	
	/**
	 * @Column(type = "float", nullable = true)
	 * @var int
	 */
	private $production;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @var Entities\Clan
	 */
	private $clan;
	
	/**
	 * Constructor
	 * @param Entites\Clan
	 * @param string
	 * @param float
	 */
	public function __construct (Clan $clan, $type, $balance = 0)
	{
		$this->clan = $clan;
		$this->type = $type;
		$this->balance = $balance;
		$this->production = 0;
	}
	
	/**
	 * Sets the balance to current value
	 * @param DateTime
	 * @return void
	 */
	public function settleBalance ($time = NULL)
	{
		if (!$time) {
			$time = new \DateTime();
		}
		$diff = $time->format('U') - $this->clearance->format('U');
		$this->balance = $this->balance + $diff * $this->production;
		$this->clearance = $time;
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
	 * Balance getter
	 * @return float
	 */
	public function getBalance ()
	{
		return $this->balance;
	}
	
	/**
	 * Does the account has balance larger than given amount?
	 * @param float
	 * @return bool
	 */
	public function has ($amount)
	{
		$this->settleBalance();
		return $this->balance >= $amount;
	}
	
	/**
	 * Pays given value from the account
	 * @param float
	 * @throws InsufficientResourcesException
	 * @return void
	 */
	public function pay ($value)
	{
		$this->settleBalance();
		$balance = $this->balance - $value;
		if ($balance < 0) {
			throw new InsufficientResourcesException;
		}
		$this->balance = $balance;
	}
	
	/**
	 * Add given value to the account
	 * @param float
	 * @return void
	 */
	public function increase ($value)
	{
		$this->balance = $this->balance + $value;
	}
	
	/**
	 * Last clearance date getter
	 * @return DateTime
	 */
	public function getClearance ()
	{
		return $this->clearance;
	}
	
	/**
	 * Clearance setter
	 * @param DateTime
	 * @return void
	 */
	public function setClearance ($clearance)
	{
		$this->clearance = $clearance;
	}
	
	/**
	 * Production getter
	 * @return float
	 */
	public function getProduction ()
	{
		return $this->production;
	}
	
	/**
	 * Production setter
	 * @param float
	 * @return void
	 */
	public function setProduction ($production, $time = NULL)
	{
		$this->settleBalance($time);
		$this->production = $production;
	}
	
	/**
	 * Clan getter
	 * @return Entities\Clan
	 */
	public function getClan ()
	{
		return $this->clan;
	}
}