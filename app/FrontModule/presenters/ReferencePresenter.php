<?php
namespace FrontModule;

class ReferencePresenter extends BasePresenter {

	public function renderDefault ()
	{
		$this->template->orderCap = $this->context->params['game']['stats']['orderCap'];
		$this->template->orderTime = $this->context->params['game']['stats']['orderTime'];
		$this->template->initialFieldsCount = $this->context->params['game']['map']['initialFieldsCount'];
		$this->template->facilityRules = $this->context->rules->getAll('facility');
		$this->template->unitRules = $this->context->rules->getAll('unit');

		$researches = array();
		foreach ($this->context->rules->getAll('research') as $key => $research){
			$researches[$research->getCategory()][$key] = $research;
		}
		$this->template->researches = $researches;

	}
}
