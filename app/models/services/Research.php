<?php
namespace Services;
use Nette\Diagnostics\Debugger;
use Nette\DateTime;
use Entities;
use InsufficientResourcesException;
use MissingDependencyException;

/**
 * Research service class
 * @author Petr Bělohlávek
 */
class Research extends BaseService
{
	/**
	 * Upgrades the research lvl by one
	 * @param string
	 * @param Entities\Clan
	 * @return void
	 */
	public function research ($type, $clan){
		$rule = $this->context->rules->get('research', $type);
		$cost = $rule->getCost(1);

		if ($this->context->model->getResourceRepository()->checkResources($clan, $cost)) {
			$researched = $this->context->model->getResearchRepository()->getResearched($clan);
			foreach ($rule->getDependencies() as $key => $dependency){
				if (!isset($researched[$key])){
					throw new \MissingDependencyException;
				}
			}

			$this->create(array(
				'owner' => $clan,
				'type' => $type,
				'level' => 1
			));

			$this->context->model->getResourceService()->pay($clan, $cost);
		}
		else{
			throw new \InsufficientResourcesException;
		}

	}

	/**
	 * Upgrades the research lvl by one
	 * @param Entities\Research
	 * @param Entities\Clan
	 * @return void
	 */
	public function upgrade ($research, $clan){
		$cost = $this->context->rules->get('research', $research->type)->getCost($research->level + 1);

		if ($this->context->model->getResourceRepository()->checkResources($clan, $cost)) {
			$this->update($research, array(
				'level' => $research->level + 1,
			));

			$this->context->model->getResourceService()->pay($clan, $cost);
		}
		else{
			throw new \InsufficientResourcesException;
		}

	}

}
