<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class ExplorationEfficiency extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Efektivita průzkumu';
	}

	public function getExplanation ()
	{
		return 'Zvýší šanci získat více surovin.';
	}

	public function getCost ($level = 1)
	{
		return array(
			'metal' => $level * 120,
			'fuel' => $level * 20,
			'food' => $level * 120
		);
	}

	public function getResearchTime ($level = 1)
	{
		return (5 + $level) * 60 * 60;
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
		return 10;
	}

	public function getCategory ()
	{
		return 'administration';
	}

	public function getValue ($level = 1)
	{
		return 200 + $level * 70;
	}

}
