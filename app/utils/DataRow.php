<?php
/**
 * An associative array with a text label
 * @author Jan "Teyras" Buchar
 */
class DataRow extends Nette\ArrayHash
{
	/** @var string */
	public $label;
	
	/**
	 * Label getter
	 * @return string
	 */
	public function getLabel ()
	{
		return $this->label;
	}
	
	/**
	 * Label setter
	 * @param string
	 * @return DataRow
	 */
	public function setLabel ($label)
	{
		$this->label = $label;
		return $this;
	}
}