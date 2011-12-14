<?php
namespace Entities;
use Nette\Utils\Json;
use Doctrine;

/**
 * A unit movement representation
 * @Entity(repositoryClass = "Repositories\Move")
 * @author Jan "Teyras" Buchar
 */
class Move extends Event
{
	/**
	 * @ManyToOne(targetEntity = "Entities\Field")
	 * @var Entities\Field
	 */
	private $origin;
	
	/**
	 * @ManyToOne(targetEntity = "Entities\Field")
	 * @var Entities\Field
	 */
	private $target;
	
	/**
	 * @OneToMany(targetEntity = "Entities\Unit", mappedBy = "move")
	 * @var Doctrine\Common\Collections\ArrayCollection
	 */
	private $units;
	
	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $cargo;
	
	/**
	 * Constructor
	 * @param string
	 * @param Entities\Clan
	 * @param Entities\Field
	 * @param Entities\Field
	 * @param array
	 */
	public function __construct ($type, Clan $owner, Field $origin, Field $target, $cargo = array())
	{
		parent::__construct($type, $owner);
		$this->units = new Doctrine\Common\Collections\ArrayCollection;
		$this->origin = $origin;
		$this->target = $target;
		$this->cargo = Json::encode($cargo);
	}
	
	/**
	 * Get clans that are relevant to this event
	 * @return array of Entities\Clan
	 */
	public function getObservers ()
	{
		return array($this->owner, $this->target->owner);
	}
	
	/**
	 * Origin getter
	 * @return Entities\Field
	 */
	public function getOrigin ()
	{
		return $this->origin;
	}
	
	/**
	 * Target getter
	 * @return Entities\Field
	 */
	public function getTarget ()
	{
		return $this->target;
	}
	
	/**
	 * Units getter
	 * @return Doctrine\Common\Collections\ArrayCollection
	 */
	public function getUnits ()
	{
		return $this->units;
	}
	
	/**
	 * A getter of unit $id => $count pairs
	 * @return array
	 */
	public function getUnitList ()
	{
		$result = array();
		foreach ($this->getUnits() as $unit) {
			$result[$unit->id] = $unit->count;
		}
		return $result;
	}
	
	/**
	 * Cargo getter
	 * @return array
	 */
	public function getCargo ()
	{
		return Json::decode($this->cargo);
	}
	
	/**
	 * Add a unit to the move
	 * @param Entities\Unit
	 * @return void
	 */
	public function addUnit (Unit $unit)
	{
		$unit->setMove($this);
	}
}