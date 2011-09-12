<?php
namespace Rules\Events;
use Rules\AbstractRule;

class Colonisation extends AbstractRule implements IEvent
{
	public function process ($id)
	{
		$move = $this->getContext()->model->getMoveRepository()->findOneByEvent($id);
		if ($move->target->owner === NULL) {
			$move->target->setOwner($move->clan);
		}
		$this->context->model->getFieldService()->invalidateVisibleFields($move->clan);
	}
}