<?php
namespace Rules\Scores;
use Rules\AbstractRule;

class Quest extends AbstractRule implements IScore
{
	public function getDescription ()
	{
		return 'Úkoly';
	}
}
