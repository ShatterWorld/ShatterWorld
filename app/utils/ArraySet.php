<?php

/**
 * ArraySet class
 * Represents a set (i.e. there are no mulitple items)
 * @author Petr Bělohlávek
 */
class ArraySet extends Nette\ArrayList
{
	/**
	 * Adds new item to the set only if the item had not been added before
	 * @param int
	 * @param mixed
	 * @return void
	 */
	public void offsetSet ($index, $value)
	{
		if (!$this->offsetExists($index)){
			parent::offsetSet($index, $value);
		}
	}
}
