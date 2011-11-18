<?php
namespace Repositories;
use Entities;
use Doctrine;
use Nette\Diagnostics\Debugger;

/**
 * Research repository class
 * @author Petr Bělohlávek
 */
class Research extends BaseRepository
{
	/**
	 * Returns already researched researches of the clan
	 * @param Entities\Clan
	 * @return array of Entities\Research
	 */
	public function getResearched ($clan)
	{
		$result = $this->findByOwner($clan->id);
		$indexedRes = array();

		foreach($result as $r){
			$indexedRes[$r->type] = $r;
		}
		return $indexedRes;
	}

	/**
	 * Returns the research specified by owner and type
	 * @param Entities\Clan
	 * @param string
	 * @return Entities\Research
	 */
	public function getClanResearch ($clan, $type)
	{
		$result = $this->findOneBy(array('owner' => $clan->id, 'type' => $type));
		return $result;
	}
	/**
	 * Returns the level of research
	 * @param Entities\Clan
	 * @param string
	 * @return int
	 */
	public function getResearchLevel ($clan, $type)
	{
		$research = $this->getClanResearch($clan, $type);

		if ($research === null){
			return 0;
		}
		return $research->level;
	}


}
