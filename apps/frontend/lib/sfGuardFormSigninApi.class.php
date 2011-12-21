<?php

/**
 * sfGuardFormSignin for sfGuardAuth signin action
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfGuardFormSignin.class.php 23536 2009-11-02 21:41:21Z Kris.Wallsmith $
 */
class sfGuardFormSigninApi extends sfGuardFormSignin
{

    /**
     * @see sfForm
     */
    public function configure()
    {
        parent::configure();
        $this->widgetSchema['username'] = new sfWidgetFormInputText(array(), array(
                    'onblur' => "this.value=this.value.replace(/\s+/g, '');"
                ));
        $this->validatorSchema->setPostValidator(new sfGuardValidatorUserApi());
    }

}