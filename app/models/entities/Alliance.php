<?php
namespace Entities;
use Doctrine;

/**
 * An alliance of clans
 * @Entity(repositoryClass = "Repositories\Alliance")
 * @author Jan "Teyras" Buchar
 */
class Alliance extends BaseEntity
{
	/**
	 * @Column(type = "string", unique = true)
	 * @var string
	 */
	private $name;

	/**
	 * @OneToMany(targetEntity = "Entities\Clan", mappedBy = "alliance")
	 * @var Doctrine\Common\Collections\ArrayCollection
	 */
	private $members;

	/**
	 * @ManyToMany(targetEntity = "Entities\Clan", mappedBy = "applications")
	 * @var Doctrine\Common\Collections\ArrayCollection
	 */
	private $applicants;

	/**
	 * @OneToOne(targetEntity = "Entities\Clan")
	 * @JoinColumn(onDelete = "CASCADE")
	 * @var Entities\Clan
	 */
	private $leader;

	/**
	 * Constructor
	 */
	public function __construct ()
	{
		$this->members = new Doctrine\Common\Collections\ArrayCollection();
		$this->applicants = new Doctrine\Common\Collections\ArrayCollection();
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
	 * Members getter
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getMembers ()
	{
		return $this->members;
	}

	/**
	 * Add an alliance member
	 * @param Entities\Clan
	 * @return void
	 */
	public function addMember (Clan $member)
	{
		$member->setAlliance($this);
	}

	/**
	 * Applicants getter
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getApplicants ()
	{
		return $this->applicants;
	}

	/**
	 * Add an applicant
	 * @param Entities\Clan
	 * @return void
	 */
	public function addApplicant (Clan $clan)
	{
		$this->applicants[] = $clan;
	}

	/**
	 * Remove an applicant
	 * @param Entities\Clan
	 * @return void
	 */
	public function removeApplicant (Clan $clan)
	{
		$this->applicants->removeElement($clan);
	}

	/**
	 * Leader getter
	 * @return Entities\Clan
	 */
	public function getLeader ()
	{
		return $this->leader;
	}

	/**
	 * Leader setter
	 * @param Entities\Clan
	 * @return void
	 */
	public function setLeader (Clan $leader)
	{
		$this->leader = $leader;
		$leader->setAlliance($this);
	}
}
