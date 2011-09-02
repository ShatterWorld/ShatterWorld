<?php
namespace Entities;

/**
 * A resource balance
 * @Entity
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
	 * @Column(type = "integer")
	 * @var int
	 */
	private $balance;
	
	/**
	 * @Column(type = "datetime")
	 * @var DateTime
	 */
	private $clearance;
	
	/**
	 * @Column(type = "integer", nullable = true)
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
	 */
	public function __construct (Clan $clan, $type)
	{
		$this->clan = $clan;
		$this->type = $type;
		$this->production = 0;
	}
	
	/**
	 * Sets the balance to current value
	 * @return void
	 */
	public function settleBalance ()
	{
		$now = new DateTime();
		$diff = $now->format('U') - $this->clearance->format('U');
		$this->balance = $this->balance + $diff * $this->production;
		$this->clearance = $now;
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
	 * @return int
	 */
	public function getBalance ()
	{
		return $this->balance;
	}
	
	/**
	 * Balance setter
	 * @param int
	 * @return void
	 */
	public function setBalance ($balance)
	{
		$this->balance = $balance;
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
	 * @return int
	 */
	public function getProduction ()
	{
		return $this->production;
	}
	
	/**
	 * Production setter
	 * @param int
	 * @return void
	 */
	public function setProduction ($production)
	{
		$this->settleBalance();
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