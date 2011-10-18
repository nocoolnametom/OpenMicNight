<?php

/**
 * message actions.
 *
 * @package    OpenMicNight
 * @subpackage message
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::automessageActions
 */
class messageActions extends automessageActions
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
        $validators['recipient_id'] = new sfValidatorDoctrineChoice(array(
                    'model' => Doctrine_Core::getTable('Message')
                            ->getRelation('sfGuardUser')
                            ->getAlias(),
                    'required' => false,
                ));
        return $validators;
    }

}
