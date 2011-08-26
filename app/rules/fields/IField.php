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
}
