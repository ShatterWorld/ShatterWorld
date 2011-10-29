<?php
namespace Entities;

/**
 * Orders of a clan
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class Orders extends BaseEntity
{
	/**
	 * @OneToOne(targetEntity = "Entities\Clan", inversedBy = "orders")
	 * @JoinColumn(onDelete = "CASCADE")
	 * @var Entities\Clan
	 */
	private $owner;
	
	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $issued;
	
	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $expired;
	
	/**
	 * Constructor
	 * @param Entities\Clan
	 */
	public function __construct (Clan $owner)
	{
		$this->owner = $owner;
		$this->issued = 0;
		$this->expired = 0;
	}
	
	/**
	 * Issued orders getter
	 * @return int
	 */
	public function getIssued ()
	{
		return $this->issued;
	}
	
	/**
	 * Issued orders setter
	 * @param int
	 * @return void
	 */
	public function setIssued ($issued)
	{
		$this->issued = $issued;
	}
	
	/**
	 * Expired orders getter
	 * @return int
	 */
	public function getExpired ()
	{
		return $this->expired;
	}
	
	/**
	 * Expired orders setter
	 * @param int
	 * @return void
	 */
	public function setExpired ($expired)
	{
		$this->expired = $expired;
	}
}