<?php
namespace Rules\Scores;
use Rules\AbstractRule;

class Territory extends AbstractRule implements IScore
{
	public function getDescription ()
	{
		return 'Území';
	}
}
