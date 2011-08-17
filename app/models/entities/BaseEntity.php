<?php
namespace Entities;
use Nette;

/**
 * Universal entity ancestor
 * @MappedSuperclass
 * @author Jan "Teyras" Buchar
 */
class BaseEntity extends Nette\Object {
	
	/**
	 * @Id @GeneratedValue
	 * @Column(type = "integer")
	 * @var int
	 */
	private $id;
	
	/**
	 * ID getter
	 * @return int
	 */
	final public function getId ()
	{
		return $this->id;
	}
}