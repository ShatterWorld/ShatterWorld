<?php
namespace Rules\Scores;
use Rules\AbstractRule;

class Unit extends AbstractRule implements IScore
{
	public function getDescription ()
	{
		return 'Jednotky';
	}
}
