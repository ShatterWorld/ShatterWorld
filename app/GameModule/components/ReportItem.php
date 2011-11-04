<?php
/**
 * An item to be rendered in a report
 * @author Jan "Teyras" Buchar
 */
class ReportItem extends Nette\Object
{
	/**
	 * The type of the item
	 * @var string
	 */
	protected $type;
	
	/**
	 * The heading of the item
	 * @var string
	 */
	protected $heading;
	
	/**
	 * The data of the item
	 * @var mixed
	 */
	protected $data;
	
	/**
	 * Constructor
	 * @param string
	 * @param mixed
	 * @param string
	 */
	public function __construct ($type, $data)
	{
		$this->type = $type;
		$this->data = $data;
	}
	
	/**
	 * A static constructor
	 * @param string
	 * @param array
	 * @return ReportItem
	 */
	public static function create ($type, $data)
	{
		return new static($type, $data);
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
	 * Heading getter
	 * @return string
	 */
	public function getHeading ()
	{
		return $this->heading;
	}
	
	/**
	 * Heading setter
	 * @param string
	 * @return ReportItem
	 */
	public function setHeading ($heading)
	{
		$this->heading = $heading;
		return $this;
	}
	
	/**
	 * Data getter
	 * @return mixed
	 */
	public function getData ()
	{
		return $this->data;
	}
}