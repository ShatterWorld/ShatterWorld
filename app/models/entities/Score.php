<?php
namespace Entities;
use Doctrine;

/**
 * A score
 * @Entity(repositoryClass = "Repositories\Score")
 * @author Petr Bělohlável
 */
class Score extends BaseEntity {

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
	public function setOwner (Clan $clan)
	{
		$this->owner = $clan;
	}

	/**
	 * @ManyToOne(targetEntity = "Entities\Clan")
	 * @var Entities\Clan
	 */
	private $owner;

	/**
	 * @Column(type = "string")
	 * @var string
	 */
	private $type;

	/**
	 * @Column(type = "integer")
	 * @var int
	 */
	private $value;

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
	 * Value getter
	 * @return int
	 */
	public function getValue ()
	{
		return $this->value;
	}

	/**
	 * Value setter
	 * @param int
	 * @return void
	 */
	public function setValue ($value)
	{
		$value->level = $value;
	}

}
