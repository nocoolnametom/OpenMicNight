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
        $validators['recipient_id'] = new sfValidatorDoctrineChoice(array(
                    'model' => Doctrine_Core::getTable('Message')
                            ->getRelation('sfGuardUser')
                            ->getAlias(),
                ));
        return $validators;
    }

    public function getUpdateValidators()
    {
        // make created_at and updated_at fields non-required
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
