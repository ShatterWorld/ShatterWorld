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
		return $level * 36000 * $this->getContext()->stats->getResearchEfficiency($this->getContext()->model->getClanRepository()->getPlayerClan());
	}

	public function getDependencies ()
	{
		return array(
			'storage' => 1
		);
	}

	public function afterResearch (Entities\Construction $construction)
	{
		# clean cache
	}

	public function getLevelCap ()
	{
		return 10;
	}

}
