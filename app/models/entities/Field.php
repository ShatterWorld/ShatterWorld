<?php
namespace Entities;
use Doctrine;

/**
 * A game plan field entity
 * @Entity(repositoryClass = "Repositories\Field")
 * @author Jan "Teyras" Buchar
 */
class Field extends BaseEntity {

	/**
	 * @ManyToOne(targetEntity = "Entities\Clan", inversedBy = "fields")
	 * @JoinColumn(onDelete = "SET NULL")
	 * @var Entities\Clan
	 */
	private $owner;

	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $coordX;

	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $coordY;

	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $type;

	/**
	 * @Column(type = "string", nullable = TRUE)
	 * @var string
	 */
	private $facility;

	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $level;
	
	/**
	 * @OneToMany(targetEntity = "Entities\Unit", mappedBy = "location")
	 * @var Doctrine\Common\Collections\ArrayCollection
	 */
	private $units;

	/**
	 * Constructor
	 */
	public function __construct ()
	{
		$this->level = 0;
		$this->units = new Doctrine\Common\Collections\ArrayCollection;
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
	 * Owner setter
	 * @param Entities\Clan
	 * @return void
	 */
	public function setOwner (Clan $clan = null)
	{
		$this->owner = $clan;
		if ($this->owner) {
			$this->owner->getFields()->removeElement($this);
		}
		if ($clan) {
			$clan->getFields()->add($this);
		}
	}

	/**
	 * Coordinates getter
	 * @return string
	 */
	public function getCoords ()
	{
		return sprintf('[%d;%d]', $this->coordX, $this->coordY);
	}

	/**
	 * X coordinate getter
	 * @return integer
	 */
	public function getX ()
	{
		return $this->coordX;
	}

	/**
	 * Y coordinate getter
	 * @return integer
	 */
	public function getY ()
	{
		return $this->coordY;
	}

	/**
	 * Coordinates setter
	 * @param int
	 * @param int
	 * @return void
	 */
	public function setCoords ($x, $y = NULL)
	{
		if (!is_scalar($x)) {
			list($x, $y) = $x;
		}
		$this->coordX = $x;
		$this->coordY = $y;
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
	 * Type setter
	 * @param string
	 * @return void
	 */
	public function setType ($type)
	{
		$this->type = $type;
	}

	/**
	 * Facility getter
	 * @return string
	 */
	public function getFacility ()
	{
		return $this->facility;
	}

	/**
	 * Facility setter
	 * @param string
	 * @return void
	 */
	public function setFacility ($facility)
	{
		$this->facility = $facility;
		$this->setLevel(0);
	}

	/**
	 * Level getter
	 * @return int
	 */
	public function getLevel ()
	{
		return $this->level;
	}

	/**
	 * Level setter
	 * @param int
	 * @return void
	 */
	public function setLevel ($level)
	{
		$this->level = $level;
	}
	
	/**
	 * Units getter
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getUnits ()
	{
		return $this->units;
	}
}
