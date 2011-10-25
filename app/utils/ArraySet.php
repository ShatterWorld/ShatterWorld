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
	 * @deprecated
	 * @param int
	 * @param mixed
	 * @return void
	 */
	public function offsetSet ($index, $value)
	{
		if (!$this->offsetExists($index)){
			parent::offsetSet($index, $value);
		}
	}

	/**
	 * Adds new item to the set only if the item had not been added before
	 * Returns true is the item is added, false otherwise
	 * @param int
	 * @param mixed
	 * @return boolean
	 */
	public function addElement ($index, $value)
	{
		if (!$this->offsetExists($index)){
			parent::offsetSet($index, $value);
			return true;
		}
		return false;
	}

	/**
	 * Deletes the element by given id
	 * Returns true is the item is deleted, false otherwise
	 * @param int
	 * @return boolean
	 */
	public function deleteElement ($index)
	{
		if ($this->offsetExists($index)){
			$this->offsetUnset($index);
			return true;
		}
		return false;
	}

	/**
	 * Updates the element
	 * @param int
	 * @param mixed
	 * @return void
	 */
	public function updateElement ($index, $value)
	{
		if ($this->deleteElement($index)){
			$this->addElement($index, $value);
			return true;
		}
		return false;
	}

	/**
	 * Joins the given ArraySet
	 * @param ArraySet
	 * @return void
	 */
	public function join ($arraySet)
	{
		foreach($arraySet as $index => $item){
			$this->addElement($index, $item);
		}
	}

	/**
	 * Returns the array representation of ArraySet
	 * @return array
	 */
	public function toArray ()
	{
		return iterator_to_array ($this->getIterator(), true);
	}

}
