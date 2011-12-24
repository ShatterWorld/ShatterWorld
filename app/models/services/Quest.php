<?php
namespace Services;
use Entities;

class Quest extends BaseService
{
	public function processCompletion (Entities\Clan $clan, $term)
	{
		foreach ($this->getRepository()->findActive($clan) as $quest) {
			$this->completeQuest($quest, $term);
		}
	}

	protected function completeQuest (Entities\Quest $quest, $term)
	{
		$rule = $this->context->rules->get('quest', $quest->type);
		if ($rule->isCompleted($quest, $term)) {
			$rule->processCompletion($quest);
			$quest->completed = TRUE;
			if ($quest->level < $rule->getLevelCap()) {
				$next = $this->create(array(
					'owner' => $quest->owner,
					'type' => $quest->type,
					'level' => $quest->level + 1,
					'start' => $term
				), FALSE);
				$this->completeQuest($next, $term);
			}
			$this->context->model->getScoreService()->increaseClanScore($quest->owner, 'quest', $rule->getValue($quest->level));
		}
	}
}
