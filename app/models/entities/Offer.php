<?php
namespace Entities;

/**
 * A offer profile entity
 * @Entity(repositoryClass = "Repositories\Offer")
 * @author Petr Bělohlávek
 */
class Offer extends BaseEntity 
{
	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @JoinColumn(onDelete = "CASCADE")
	 * @var Entities\Clan
	 */
	private $owner;

	/**
	 * @Column(type = "boolean")
	 * @var boolean
	 */
	private $sold;

	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $offer;

	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $offerAmount;

	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $demand;

	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $demandAmount;

	public function __construct ($owner, $offer, $demand)
	{
		$this->owner = $owner;
		$this->offer = $offer;
		$this->demand = $demand;
		$this->sold = false;
	}
	/**
	 * Owner getter
	 * @return Entities\Clan
	 */
	public function getOwner ()
	{
		return $this->owner;
	}

	/**
	 * Offer getter
	 * @return string
	 */
	public function getOffer ()
	{
		return $this->offer;
	}

	/**
	 * OfferAmount getter
	 * @return int
	 */
	public function getOfferAmount ()
	{
		return $this->offerAmount;
	}

	/**
	 * OfferAmount setter
	 * @param int
	 * @return void
	 */
	public function setOfferAmount ($offerAmount)
	{
		$this->offerAmount = $offerAmount;
	}

	/**
	 * Demand getter
	 * @return string
	 */
	public function getDemand ()
	{
		return $this->demand;
	}

	/**
	 * DemandAmount getter
	 * @return int
	 */
	public function getDemandAmount ()
	{
		return $this->demandAmount;
	}

	/**
	 * DemandAmount setter
	 * @param int
	 * @return void
	 */
	public function setDemandAmount ($demandAmount)
	{
		$this->demandAmount = $demandAmount;
	}

	/**
	 * Sold getter
	 * @return boolean
	 */
	public function isSold ()
	{
		return $this->sold;
	}

	/**
	 * Sold setter
	 * @param boolean
	 * @return void
	 */
	public function setSold ($sold)
	{
		$this->sold = $sold;
	}

}
