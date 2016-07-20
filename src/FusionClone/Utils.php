<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 20/06/2015
 * Time: 17:38
 */

namespace FusionClone;

class Utils
{
    static function getChoicesArray($objects, $optAsValue = false)
    {
        $choices = array();
        for ($i = 0; $i < count($objects); ++$i) {
            if (!$optAsValue) {
                $choices[$objects[$i]->getId()] = $objects[$i];
            } else {
                $choices[$objects[$i]->__toString()] = $objects[$i];
            }
        }

        return $choices;
    }
}