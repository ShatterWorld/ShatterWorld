<?php
namespace Rules\Researches;
use Rules\AbstractRule;
use Entities;

class Trading extends AbstractRule implements IResearch
{
	public function getDescription ()
	{
		return 'Obchod';
	}

	public function getCost ($level = 1)
	{
		return array(
			'food' => pow($level, 2) * 500
		);
	}

	public function getResearchTime ($level = 1)
	{
		return $level * 36000;
	}

	public function getDependencies ()
	{
		$res = $this->getContext()->rules->getAll('research');

		return $res;
	}

	public function afterResearch (Entities\Construction $construction)
	{
		# clean cache
	}

	public function getLevelCap (){
		return 10;
	}

}
