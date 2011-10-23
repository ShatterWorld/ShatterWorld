<?php

class ArraySet extends Nette\ArrayList
{
	public void offsetSet (int $index, mixed $value)
	{
		if (!$this->offsetExists($index)){
			parent::offsetSet($index, $value);
		}
	}
}
