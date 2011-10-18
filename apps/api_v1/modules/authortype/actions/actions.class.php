<?php

/**
 * authortype actions.
 *
 * @package    OpenMicNight
 * @subpackage authortype
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autoauthortypeActions
 */
class authortypeActions extends autoauthortypeActions
{
    
    public function getUpdateValidators()
    {
        $validators = parent::getUpdateValidators();
        $validators['name'] = new sfValidatorString(array(
            'max_length' => 255,
            'required' => false,
            ));
        return $validators;
    }
    
}
