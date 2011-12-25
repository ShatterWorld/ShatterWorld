<?php
namespace Rules\Scores;
use Rules\AbstractRule;

class Facility extends AbstractRule implements IScore
{
	public function getDescription ()
	{
		return 'Budovy';
	}
}
