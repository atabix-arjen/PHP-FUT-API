<?php
/**
 * @author JKetelaar
 */

namespace JKetelaar\fut\bot\market\items\misc;

use JKetelaar\fut\bot\ImprovedEnum;
use MyCLabs\Enum\Enum;

class Formation extends ImprovedEnum {

    const F443 = '4-4-3';
    const F41212 = '4-1-2-1-2';
    const F4231 = '4-2-3-1';

    const _DEFAULT = Formation::F443;

    public static function findByValue($value, $returnObject = false, $default = null) {
        return parent::findByValue($value, $returnObject, self::_DEFAULT());
    }

}