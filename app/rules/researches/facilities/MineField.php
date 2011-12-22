<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class MineField extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Minové pole';
	}

	public function getExplanation ()
	{
		return 'Vyzkoumání minového pole';
	}

	public function getCost ($level = 1)
	{
		return array(
			'stone' => pow($level, 2) * 500,
			'food' => pow($level, 2) * 500,
			'metal' => pow($level, 3) * 500
		);
	}

	public function getResearchTime ($level = 1)
	{
		return $level * 100000;
	}

	public function getDependencies ()
	{
		return array(
			'constructionTechnology' => 3
		);
	}

	public function getConflicts ()
	{
		return array();
	}

	public function afterResearch (Entities\Construction $construction){}

	public function getLevelCap ()
	{
		return 1;
	}

	public function getCategory ()
	{
		return 'facility';
	}

	public function getValue ($level = 1)
	{
		return 100 + $level * 50;
	}

}
