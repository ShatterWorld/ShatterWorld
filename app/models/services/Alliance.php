<?php
namespace Services;
use Entities;
use Nette\Diagnostics\Debugger;
use Exception;
use RuleViolationException;

class Alliance extends BaseService
{
	public function delete ($object, $flush = TRUE)
	{
		foreach ($object->getMembers() as $member) {
			$member->setAlliance(NULL);
			foreach ($this->context->model->getClanRepository()->findByAllianceApplication($object->id) as $applicant) {
				$applicant->allianceApplication = NULL;
			}
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

	public function leaveAlliance (Entities\Alliance $alliance, Entities\Clan $clan)
	{
		if ($clan->alliance == $alliance){
			$this->update($clan, array('alliance' => NULL));
			$this->context->model->getFieldService()->invalidateVisibleFields($clan->id);

			$members = $alliance->getMembers();
			$newLeader = null;
			$i = 0;
			foreach ($members as $member) {
				if ($newLeader === null){
					$newLeader = $member;
				}
				$i++;
				$this->context->model->getFieldService()->invalidateVisibleFields($member->id);
			}

			if ($i > 0){
				$this->update($alliance, array('leader' => $newLeader));
			}
			else{
				$this->delete($alliance);
			}

		}
		else{
			throw new RuleViolationException();
		}
	}

	public function declineApplication (Entities\Alliance $alliance, Entities\Clan $clan, $flush = TRUE)
	{
		$clan->removeApplication($alliance);
		if ($flush) {
			$this->entityManager->flush();
		}
	}
	
	public function fireMember (Entities\Alliance $alliance, Entities\Clan $clan)
	{
		if ($alliance !== $clan->alliance){
			throw new RuleViolationException();
		}

		$this->update($clan, array('alliance' => NULL));
		$this->context->model->getFieldService()->invalidateVisibleFields($clan->id);
		foreach ($alliance->getMembers() as $member) {
			$this->context->model->getFieldService()->invalidateVisibleFields($member->id);
		}
	}

	public function handOverLeadership (Entities\Alliance $alliance, Entities\Clan $clan)
	{
		if ($alliance !== $clan->alliance){
			throw new RuleViolationException();
		}

		$this->update($alliance, array('leader' => $clan));
	}

}
