<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Militia extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Domobrana';
	}

	public function getExplanation ()
	{
		return 'Umožní domobraně útočit a zvyšuje její sílu';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => $level * 90,
			'metal' => $level * 70,
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (2 + $level) * 60 * 60;
	}

	public function getDependencies ()
	{
		return array();
	}

	public function getConflicts ()
	{
		return array();
	}

	public function afterResearch (Entities\Construction $construction){}

	public function getLevelCap (){
		return 3;
	}

	public function getCategory ()
	{
		return 'unit';
	}

	public function getValue ($level = 1)
	{
		return 100 + $level * 100;
	}
}

