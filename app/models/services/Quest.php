<?php
namespace Services;
use Entities;

class Quest extends BaseService
{
	public function processCompletion (Entities\Clan $clan, $term)
	{
		foreach ($this->getRepository()->findActive($clan) as $quest) {
			$rule = $this->context->rules->get('quest', $quest->type);
			if ($rule->isCompleted($quest, $term)) {
				$this->completeQuest($quest, $term);
			}
		}
	}
	
	protected function completeQuest (Entities\Quest $quest, $term)
	{
		$rule = $this->context->rules->get('quest', $quest->type);
		$quest->processCompletion($quest);
		$quest->completed = TRUE;
		if ($quest->level < $rule->getLevelCap()) {
			$this->create(array(
				'owner' => $quest->owner,
				'type' => $quest->type,
				'level' => $quest->level + 1,
				'start' => $term
			), FALSE);
		}
	}
}