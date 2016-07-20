<?php
/**
 * Created by PhpStorm.
 * User: Many
 * Date: 6/10/14
 * Time: 7:21
 */

namespace FusionClone\OtrosBundle\OverridenClasses;


use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

class ChoiceListStringValue extends ChoiceList {
    protected function createValue($choice){
        return  strval($choice);
    }
} 