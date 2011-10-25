<?php
namespace Entities;
use Nette\Utils\Json;

/**
 * A shipment of resources
 * @Entity
 * @author Jan "Teyras" Buchar
 */
class Shipment extends Event
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
		$this->origin = $origin;
		$this->target = $target;
		$this->cargo = Json::encode($cargo);
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
	 * Cargo getter
	 * @return array
	 */
	public function getCargo ()
	{
		return Json::decode($this->cargo);
	}
}