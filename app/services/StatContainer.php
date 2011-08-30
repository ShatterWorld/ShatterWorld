<?php

/**
 * A game stat container, which provides access to values which are results of rules interaction
 * @author Jan "Teyras" Buchar
 */
class StatContainer extends Nette\Object
{
	/** @var Nette\DI\Container */
	protected $context;
	
	/**
	 * Constructor
	 * @param Nette\DI\Container
	 */
	public function __construct (Nette\DI\Container $context)
	{
		$this->context = $context;
	}
	
	/**
	 * Returns how many fields in the distance can the clan see from its territory
	 * @param Entities\Clan
	 * @return int
	 */
	public function getVisibilityRadius (Entities\Clan $clan)
	{
		return $this->context->params['game']['stats']['baseLOS'];
	}
	
	/**
	 * Calculate the time needed to colonise a field
	 * @param Entities\Field
	 * @param Entities\Field
	 * @return int
	 */
	public function getColonisationTime ($origin, $target)
	{
		$base = $this->context->params['game']['stats']['baseColonisationTime'];
		$distance = $this->context->model->getFieldRepository()->calculateDistance($origin, $target);
		$count = $this->context->model->getFieldRepository()->getTerritorySize($origin->owner);
		return $base * $distance * $count;
	}
}