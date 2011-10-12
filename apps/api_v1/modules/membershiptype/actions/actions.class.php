<?php

/**
 * membershiptype actions.
 *
 * @package    OpenMicNight
 * @subpackage membershiptype
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::automembershiptypeActions
 */
class membershiptypeActions extends automembershiptypeActions
{

    public function getCreateValidators()
    {
        // make created_at and updated_at fields non-required
        $validators = parent::getCreateValidators();
        $validators['created_at'] = new sfValidatorDateTime(array(
                    'required' => false,
                ));
        $validators['updated_at'] = new sfValidatorDateTime(array(
                    'required' => false,
                ));
        return $validators;
    }
    
    public function getUpdateValidators()
    {
        // make created_at and updated_at fields non-required
        $validators = parent::getUpdateValidators();
        $validators['name'] = new sfValidatorString(array(
            'max_length' => 255,
            'required' => false,
            ));
        return $validators;
    }
    
}
