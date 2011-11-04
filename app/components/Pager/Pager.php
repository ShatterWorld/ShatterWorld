<?php
/**
 * A paging control
 * @author Jan "Teyras" Buchar
 */
class Pager extends Nette\Application\UI\Control
{
	/** @var int */
	public $page;
	
	/** @var Nette\Utils\Paginator */
	protected $paginator;
	
	/**
	 * Set the pager up
	 * @param int
	 * @param int
	 * @param int
	 * @return void
	 */
	public function setup ($count, $length, $page = 1)
	{
		$this->paginator = new Nette\Utils\Paginator;
		$this->paginator->setItemCount($count);
		$this->paginator->setItemsPerPage($length);
		$this->paginator->setPage($page);
	}
	
	/**
	 * Get pages to be displayed in the pager
	 * @return array
	 */
	public function getSteps ()
	{
		$page = $this->paginator->page;
		$steps = array();
		foreach ($pages = range($this->paginator->firstPage, $this->paginator->lastPage) as $step) {
			if ($step < 1 + 2 || $step > end($pages) - 2 || in_array($step, range($page - 1, $page +1))) {
				$steps[] = $step;
			}
		}
		return $steps;
	}
	
	/**
	 * Render the control
	 * @return void
	 */
	public function render ()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/pager.latte');
		$template->steps = $this->getSteps();
		$template->paginator = $this->paginator;
		$template->render();
	}
}
?>