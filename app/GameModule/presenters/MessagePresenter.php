<?php
namespace GameModule;
use Nette;
use Nette\Utils\Strings;
use Nette\Application\UI\Form;
use Nette\Diagnostics\Debugger;
use InsufficientResourcesException;
use RuleViolationException;


/**
 * A message presenter
 * @author Petr Bělohlávek
 */
class MessagePresenter extends BasePresenter {

	protected $recipients;

	public function renderNew ($recipients = null)
	{
		$this->recipients = $recipients;
	}

	/**
	 * Creates the new message form
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentNewMessageForm ()
	{
		$form = new Form();

		$form->addText('recipient', 'Nick příjemce')
			->setRequired('Musíte vybrat příjemce');

		$form->addText('subject', 'Předmět')
			->setRequired('Vyplňte, prosím, předmět');

		$form->addTextArea('body', 'Zpráva')
			->setRequired('Vyberte, prosím, zprávu.');

		if ($this->recipients !== null){
			$form->setDefaults(array(
				'recipient' => $this->recipients
			));
		}

		$form->addSubmit('submit', 'Odeslat');
		$form->onSuccess[] = callback($this, 'submitNewMessageForm');
		return $form;
	}

	/**
	 * New message slot
	 * @param Nette\Application\UI\Form
	 * @return void
	 */
	public function submitNewMessageForm (Form $form)
	{
		$data = $form->getValues();
		$data['sender'] = $this->getPlayerClan()->user;

		$recipientNicks = Strings::split($data['recipient'], '~[,|;| ]+\s*~');

		foreach($recipientNicks as $recipientNick){
			$recipient = $this->getUserRepository()->findOneByNickname($recipientNick);
			if ($recipient === null){
				$this->flashMessage('Příjemce '.$recipientNick.' neexistuje', 'error');
				return;
			}
			$recipients[] = $recipient;
		}

		foreach($recipients as $recipient){
			$data['recipient'] = $recipient;
			$this->getMessageService()->create($data);
		}

		$this->flashMessage('Úspěšně odesláno '.count($recipients).' zpráv.');
		$this->redirect('Message:sent');
	}


	/**
	 * Displays received messages
	 * @return void
	 */
	public function renderDefault ()
	{
		$this->template->messages = $this->getMessageRepository()->getReceivedMessages($this->getPlayerClan()->user->id);
		$this->template->dateFormat = $this->context->params['game']['setting']['dateFormat']['message']['received'];
	}

	/**
	 * Displays sent messages
	 * @return void
	 */
	public function renderSent ()
	{
		$this->template->messages = $this->getMessageRepository()->getSentMessages($this->getPlayerClan()->user->id);
		$this->template->dateFormat = $this->context->params['game']['setting']['dateFormat']['message']['sent'];
	}

	/**
	 * Checks permissions
	 * @param int
	 * @return void
	 */
	public function actionShow ($messageId)
	{
		try{
			$this->getMessageRepository()->checkPermission($this->getPlayerClan()->user, $this->getMessageRepository()->find($messageId));
		}
		catch (RuleViolationException $e){
			$this->flashMessage('Nemůžete číst zprávy, které nejsou vaše', 'error');
			$this->redirect('Message:');
		}

/*
		$message = $this->getMessageRepository()->find($messageId);
		if(!($message !== null && ($message->recipient == $this->getPlayerClan()->user || $message->sender == $this->getPlayerClan()->user))){
			$this->flashMessage('Zpráva s tímto ID není vaše', 'error');
			$this->redirect('Message:');
		}
*/
	}

	/**
	 * Displays the details of the message
	 * @param int
	 * @return void
	 */
	public function renderShow ($messageId)
	{
		$message = $this->getMessageRepository()->find($messageId);
		$this->template->message = $message;
		$this->template->dateFormat = $this->context->params['game']['setting']['dateFormat']['message']['show'];
		$this->getMessageService()->update($message, array('read' => true));
	}

	/**
	 * Marks the message as read
	 * @param int
	 * @return void
	 */
	public function handleMarkRead ($messageId)
	{
		try{
			$this->getMessageService()->markRead($this->getPlayerClan()->user, $this->getMessageRepository()->find($messageId), TRUE);
			$this->flashMessage('Označeno jako přečtená');
		}
		catch (RuleViolationException $e){
			$this->flashMessage('Nemůžete číst zprávy, které nejsou vaše', 'error');
		}

		$this->redirect('this');
	}

	/**
	 * Marks the message as unread
	 * @param int
	 * @return void
	 */
	public function handleMarkUnread ($messageId)
	{
		try{
			$this->getMessageService()->markRead($this->getPlayerClan()->user, $this->getMessageRepository()->find($messageId), FALSE);
			$this->flashMessage('Označeno jako přečtená');
		}
		catch (RuleViolationException $e){
			$this->flashMessage('Nemůžete číst zprávy, které nejsou vaše', 'error');
		}

		$this->redirect('this');
	}

	/**
	 * Deletes the message
	 * @param int
	 * @return void
	 */
	public function handleDeleteByRecipient ($messageId)
	{
		try{
			$this->getMessageService()->deleteByRecipient($this->getPlayerClan()->user, $this->getMessageRepository()->find($messageId));
			$this->flashMessage('Smazáno');
		}
		catch (RuleViolationException $e){
			$this->flashMessage('Nemůžete mazat zprávy, které nejsou vaše', 'error');
		}

		$this->redirect('this');
	}

	/**
	 * Deletes the message
	 * @param int
	 * @return void
	 */
	public function handleDeleteBySender ($messageId)
	{
		try{
			$this->getMessageService()->deleteBySender($this->getPlayerClan()->user, $this->getMessageRepository()->find($messageId));
			$this->flashMessage('Smazáno');
		}
		catch (RuleViolationException $e){
			$this->flashMessage('Nemůžete mazat zprávy, které nejsou vaše', 'error');
		}

		$this->redirect('this');
	}




}
