<?php
namespace Repositories;
use Entities;
use Doctrine;

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
	public function getResearched (Entities\Clan $clan)
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
	public function getClanResearch (Entities\Clan $clan, $type)
	{
		$qb = $this->createQueryBuilder('r');
		$qb->where($qb->expr()->andX(
			$qb->expr()->eq('r.owner', '?0'),
			$qb->expr()->eq('r.type', '?1')
		));
		$qb->setParameters(array($clan->id, $type));
		$query = $qb->getQuery();
		$query->useResultCache(true);
		try {
			return $query->getSingleResult();
		} catch (Doctrine\ORM\NoResultException $e) {
			return null;
		}
	}
	/**
	 * Returns the level of research
	 * @param Entities\Clan
	 * @param string
	 * @return int
	 */
	public function getResearchLevel (Entities\Clan $clan, $type)
	{
		$research = $this->getClanResearch($clan, $type);

		if ($research === null){
			return 0;
		}
		return $research->level;
	}


}
