<?php
namespace GameModule;
use Nette;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
use MissingDependencyException;
use ConflictException;


/**
 * A quest presenter
 * @author Petr Bělohlávek
 */
class QuestPresenter extends BasePresenter
{
	/**
	 * Displays quests
	 * @return void
	 */
	public function renderDefault ()
	{
		$clan = $this->getPlayerClan();
		Debugger::barDump($this->getQuestRepository()->findCompleted($clan));
		$this->template->all = $this->context->rules->getAll('quest');
		$this->template->active = $this->getQuestRepository()->findActive($clan);
		$this->template->completed = $this->getQuestRepository()->findCompleted($clan);
	}




}
