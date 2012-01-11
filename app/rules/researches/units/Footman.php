<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Footman extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Pěšák';
	}

	public function getExplanation ()
	{
		return 'Síla pěšáků';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => $level * 100,
			'metal' => $level * 90,
			'stone' => $level * 120,
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
		return 300 + $level * 150;
	}
}

