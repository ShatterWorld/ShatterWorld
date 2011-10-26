<?php
namespace Entities;
use Doctrine;

/**
 * A clan entity
 * @Entity(repositoryClass = "Repositories\Clan")
 * @author Jan "Teyras" Buchar
 */
class Clan extends BaseEntity {

	/**
	 * @Column(type = "string", unique = true)
	 * @var string
	 */
	private $name;

	/**
	 * @OneToOne(targetEntity = "Entities\User")
	 * @var Entities\User
	 */
	private $user;

	/**
	 * @OneToMany(targetEntity = "Entities\Field", mappedBy = "owner")
	 * @var Doctrine\Common\Collections\ArrayCollection
	 */
	private $fields;

	/**
	 * @OneToOne(targetEntity = "Entities\Field")
	 * @var Entities\Field
	 */
	private $headquarters;

	/**
	 * @ManyToOne(targetEntity = "Entities\Alliance", inversedBy = "members")
	 * @var Entities\Alliance
	 */
	private $alliance;

	/**
	 * @ManyToOne(targetEntity = "Entities\Alliance")
	 * @JoinColumn(onDelete = "SET NULL")
	 * @var Entities\Alliance
	 */
	private $allianceApplication;

	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $issuedOrders;

	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $expiredOrders;

	/**
	 * @Column(type = "boolean")
	 * @var bool
	 */
	private $deleted;

	/**
	 * Constructor
	 * @param Entities\User
	 */
	public function __construct (User $user)
	{
		$this->fields = new Doctrine\Common\Collections\ArrayCollection();
		$this->user = $user;
		$this->issuedOrders = 0;
		$this->expiredOrders = 0;
		$this->deleted = FALSE;
	}

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
	 * Fields getter
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getFields ()
	{
		return $this->fields;
	}

	/**
	 * Add a field to the clan's territory
	 * @param Entities\Field
	 * @return void
	 */
	public function addField (Field $field)
	{
		$field->setOwner($this);
	}

	/**
	 * Headquarters getter
	 * @return Entities\Field
	 */
	public function getHeadquarters ()
	{
		return $this->headquarters;
	}

	/**
	 * Headquarters setter
	 * @param Entities\Field
	 * @return void
	 */
	public function setHeadquarters (Field $headquarters)
	{
		$headquarters->setOwner($this);
		$headquarters->facility = 'headquarters';
		$this->headquarters = $headquarters;
	}

	/**
	 * Alliance getter
	 * @return Entities\Alliance
	 */
	public function getAlliance ()
	{
		return $this->alliance;
	}

	/**
	 * Alliance setter
	 * @param Entities\Alliance
	 * @return void
	 */
	public function setAlliance (Alliance $alliance = NULL)
	{
		$this->alliance = $alliance;
		if ($this->alliance) {
			$this->alliance->getMembers()->removeElement($this);
		}
		if ($alliance) {
			$alliance->getMembers()->add($this);
		}
	}

	/**
	 * Alliance application getter
	 * @return Entities\Alliance
	 */
	public function getAllianceApplication ()
	{
		return $this->allianceApplication;
	}

	/**
	 * Alliance setter
	 * @param Entities\Alliance
	 * @return void
	 */
	public function setAllianceApplication (Alliance $alliance = NULL)
	{
		$this->allianceApplication = $alliance;
	}

	/**
	 * Issued orders getter
	 * @return int
	 */
	public function getIssuedOrders ()
	{
		return $this->issuedOrders;
	}

	/**
	 * Issued orders setter
	 * @param int
	 * @return void
	 */
	public function setIssuedOrders ($issuedOrders)
	{
		$this->issuedOrders = $issuedOrders;
	}

	/**
	 * Expired orders getter
	 * @return int
	 */
	public function getExpiredOrders ()
	{
		return $this->issuedOrders;
	}

	/**
	 * Expired orders setter
	 * @param int
	 * @return void
	 */
	public function setExpiredOrders ($expiredOrders)
	{
		$this->expiredOrders = $expiredOrders;
	}

	/**
	 * Has the clan been deleted?
	 * @return bool
	 */
	public function isDeleted ()
	{
		return $this->deleted;
	}

	/**
	 * Deleted setter
	 * @param bool
	 * @return void
	 */
	public function setDeleted ($deleted)
	{
		$this->deleted = $deleted;
	}
}
