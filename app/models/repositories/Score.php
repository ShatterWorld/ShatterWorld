<?php
namespace Repositories;
use Entities;
use Doctrine;
use Nette\Diagnostics\Debugger;

/**
 * Score repository class
 * @author Petr Bělohlávek
 */
class Score extends BaseRepository
{
	/**
	 *	Get total clan score
	 * 	@param Entities\Clan
	 * 	@return int
	 */
	public function getTotalClanScore (Entities\Clan $clan)
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('sum(s.value) as sumvalue')
			->from('Entities\Score', 's')
			->where(
				$qb->expr()->eq('s.owner', $clan->id)
			);
		$result = $qb->getQuery()->getResult();
		return $result[0]['sumvalue'];
	}

	/**
	 *	Get clan score
	 * 	@param Entities\Clan
	 *	@param string
	 * 	@return int
	 */
	public function getClanScore (Entities\Clan $clan, $type)
	{
		$score = $this->findOneBy(array(
			'owner' => $clan->id,
			'type' => $type
		));
		return $score->value;
	}

	/**
	 *	Get clan score array
	 * 	@param Entities\Clan
	 * 	@return array of int
	 */
	public function getClanScoreArray (Entities\Clan $clan)
	{
		$scores = $this->findByOwner($clan->id);

		$scoreArray = array();
		foreach($scores as $score){
			$scoreArray[$score->type] = $score->value;
		}
		return $scoreArray;
	}

	/**
	 *	Get total alliance score
	 * 	@param Entities\Alliance
	 * 	@return int
	 */
	public function getTotalAllianceScore (Entities\Alliance $alliance)
	{
		foreach ($alliance->members as $member){
			$memberIds[] = $member->id;
		}

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('sum(s.value) as sumvalue')
			->from('Entities\Score', 's')
			->where(
				$qb->expr()->in('s.owner', $memberIds)
			);
		$result = $qb->getQuery()->getResult();
		return $result[0]['sumvalue'];
	}


	/**
	 *	Get alliance score array
	 * 	@param Entities\Alliance
	 * 	@return array of array of int
	 */
	public function getAllianceScoreArray (Entities\Alliance $alliance)
	{
		foreach ($alliance->members as $member){
			$memberIds[] = $member->id;
		}

		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('o.id, sum(s.value) as sumvalue')
			->from('Entities\Score', 's')
			->leftJoin('s.owner', 'o')
			->where(
				$qb->expr()->in('s.owner', $memberIds)
			)
			->groupBy('s.owner')
			->orderBy('sumvalue', 'DESC');
		$scores = $qb->getQuery()->getResult();

		$allianceScore = array();
		foreach ($scores as $score){
			$allianceScore[$score['id']] = $score['sumvalue'];
		}

		return $allianceScore;
	}

	/**
	 *	Get clan score chart
	 * 	@return array
	 */
	public function getClanChart ()
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('o.id as clanid, o.name as clanname, u.id as userid, u.nickname as usernickname, sum(s.value) as sumvalue')
			->from('Entities\Score', 's')
			->leftJoin('s.owner', 'o')
			->leftJoin('o.user', 'u')
			->groupBy('s.owner')
			->orderBy('sumvalue', 'DESC')
			->addOrderBy('o.name');
		return $qb->getQuery()->getResult();
	}

	/**
	 *	Get alliance score chart
	 * 	@return array
	 */
	public function getAllianceChart ()
	{
		$qb = $this->getEntityManager()->createQueryBuilder();
		$qb->select('a.id as allianceid, a.name as alliancename, l.name as leadername, l.id as leaderid, sum(s.value) as sumvalue')
			->from('Entities\Score', 's')
			->leftJoin('s.owner', 'o')
			->leftJoin('o.alliance', 'a')
			->leftJoin('a.leader', 'l')
			->where(
				$qb->expr()->isNotNull('o.alliance')
			)
			->groupBy('o.alliance')
			->orderBy('sumvalue', 'DESC')
			->addOrderBy('a.name');
		return $qb->getQuery()->getResult();

	}


}
