<?php
namespace Rules\Researches;
use Rules\AbstractRule;

class Storage extends AbstractRule implements IResearch
{
	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 250,
			'stone' => pow($level, 2) * 250,
			'metal' => pow($level, 2) * 250,
		);
	}
	
	public function getResearchTime ($level = 1)
	{
		return $level * 18000;
	}
	
	public function getDependencies ()
	{
		return array();
	}
}