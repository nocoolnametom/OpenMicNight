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

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.');
        return true;
    } 

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
