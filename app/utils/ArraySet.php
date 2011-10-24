<?php

/**
 * ArraySet class
 * Represents a set (i.e. there are no mulitple items)
 * @author Petr Bělohlávek
 */
class ArraySet extends Nette\ArrayHash
{
	/**
	 * Adds new item to the set only if the item had not been added before
	 * Returns true is the iten is added, false otherwise
	 * @param int
	 * @param mixed
	 * @return boolean
	 */
	public function offsetSet ($index, $value)
	{
		if (!$this->offsetExists($index)){
			parent::offsetSet($index, $value);
			return true;
		}
		return false;
	}
}
