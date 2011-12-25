<?php
namespace Rules\Scores;
use Rules\AbstractRule;

class Production extends AbstractRule implements IScore
{
	public function getDescription ()
	{
		return 'Produkce';
	}
}
