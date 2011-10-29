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
	 * @ManyToOne(targetEntity = "Entities\User")
	 * JoinColumn(onDelete = "CASCADE")
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
	 * @JoinColumn(onDelete = "SET NULL")
	 * @var Entities\Alliance
	 */
	private $alliance;

	/**
	 * @ManyToMany(targetEntity = "Entities\Alliance", inversedBy = "applicants")
	 * @JoinTable(name = "AllianceApplication")
	 * @var Entities\Alliance
	 */
	private $applications;

	/**
	 * @OneToOne(targetEntity = "Entities\Orders", mappedBy = "owner")
	 * @var Entities\Orders
	 */
	private $orders;
	
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
		$this->applications = new Doctrine\Common\Collections\ArrayCollection();
		$this->user = $user;
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
		if ($this->alliance) {
			$this->alliance->getMembers()->removeElement($this);
		}
		$this->alliance = $alliance;
		if ($alliance) {
			$alliance->getMembers()->add($this);
			$this->applications->clear();
		}
	}

	/**
	 * Alliance applications getter
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getApplications ()
	{
		return $this->applications;
	}

	/**
	 * Add an alliance application
	 * @param Entities\Alliance
	 * @return void
	 */
	public function addApplication (Alliance $alliance)
	{
		$alliance->addApplicant($this);
		$this->applications[] = $alliance;
	}

	/**
	 * Orders getter
	 * @return Entities\Orders
	 */
	public function getOrders ()
	{
		return $this->orders;
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
