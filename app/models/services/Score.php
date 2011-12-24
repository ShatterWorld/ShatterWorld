<?php
namespace Services;
use Nette\Caching\Cache;
use Nette\Diagnostics\Debugger;
use Entities;

/**
 * Score service class
 * @author Petr Bělohlávek
 */
class Score extends BaseService
{
	/**
	 *	Increase score
	 * 	@param Entities\Clan
	 *	@param string
	 * 	@param int
	 * 	@return void
	 */
	public function increaseClanScore (Entities\Clan $clan, $type, $value)
	{
		$score = $this->context->model->getScoreRepository()->findOneBy(array(
			'owner' => $clan->id,
			'type' => $type
		));

		$this->update($score, array(
			'value' => $score->value + $value
		));
	}

	/**
	 *	Decrease score
	 * 	@param Entities\Clan
	 *	@param string
	 * 	@param int
	 * 	@return void
	 */
	public function decreaseClanScore (Entities\Clan $clan, $type, $value)
	{
		$score = $this->context->model->getScoreRepository()->findOneBy(array(
			'owner' => $clan->id,
			'type' => $type
		));

		$this->update($score, array(
			'value' => max(0, $score->value - $value)
		));
	}

}
