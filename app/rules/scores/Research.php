<?php
namespace Rules\Scores;
use Rules\AbstractRule;

class Research extends AbstractRule implements IScore
{
	public function getDescription ()
	{
		return 'Výzkum';
	}
}
