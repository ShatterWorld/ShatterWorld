<?php
namespace Entities;

/**
 * A offer profile entity
 * @Entity
 * @author Petr Bělohlávek
 */
class Offer extends BaseEntity {


	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @var Entities\Clan
	 */
	private $owner;

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


	/**
	 * Owner getter
	 * @return Entities\Clan
	 */
	public function getOwner ()
	{
		return $this->owner;
	}

	/**
	 * Owner setter
	 * @param Entities\Clan
	 * @return void
	 */
	public function setOwner ($owner)
	{
		$this->owner = $owner;
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
	 * Name setter
	 * @param string
	 * @return void
	 */
	public function setOffer ($owner)
	{
		$this->owner = $owner;
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
	 * Name setter
	 * @param string
	 * @return void
	 */
	public function setDemand ($owner)
	{
		$this->owner = $owner;
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

}
