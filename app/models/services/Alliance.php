<?php
namespace Services;
use Entities;

class Alliance extends BaseService
{
	public function delete ($object, $flush = TRUE)
	{
		foreach ($object->getMembers() as $member) {
			$member->setAlliance(NULL);
		}
		parent::delete($object, $flush);
	}
	
	public function addMember (Entities\Alliance $alliance, Entities\Clan $clan)
	{
		$this->update($clan, array('alliance' => $alliance, 'allianceApplication' => NULL));
		$this->context->model->getFieldService()->invalidateVisibleFields($clan->id);
		foreach ($alliance->getMembers() as $member) {
			$this->context->model->getFieldService()->invalidateVisibleFields($member->id);
		}
	}
}