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
	public function __construct ($type, $data, $heading = NULL)
	{
		$this->type = $type;
		$this->heading = $heading;
		$this->data = $data;
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
	 * Data getter
	 * @return mixed
	 */
	public function getData ()
	{
		return $this->data;
	}
}