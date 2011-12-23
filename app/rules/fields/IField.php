<?php
namespace Rules\Fields;

/**
 * A game map field
 * @author Jan "Teyras" Buchar
 */
interface IField extends \Rules\IRule {

    /**
     * The higher number this method returns, the more of these fields will be generated on the map
     * @return int
     */
    public function getProbability();

    /**
     * Percentual chance of this type of field having an oil field
     * @return int between 0 and 100
     */
    public function getOilProbability ();

    /**
     * Percentual bonuses to production of certain resources on this type of field
     * @return array of $resource => $bonus
     */
    public function getProductionBonuses ();

	/**
	 * Percentual bonus to the defence
	 * return float
	 */
	public function getDefenceBonus ();

	/**
	 * Value of the field
	 * return float
	 */
	public function getValue ();
}
