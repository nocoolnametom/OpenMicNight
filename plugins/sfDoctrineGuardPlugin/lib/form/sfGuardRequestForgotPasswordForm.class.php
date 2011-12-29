<?php

/**
 * BasesfGuardRequestForgotPasswordForm for requesting a forgot password email
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage form
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: BasesfGuardRequestForgotPasswordForm.class.php 23536 2009-11-02 21:41:21Z Kris.Wallsmith $
 */
class sfGuardRequestForgotPasswordForm extends sfForm
{

    /**
     * @see sfForm
     */
    public function configure()
    {
        $this->widgetSchema['email_address'] = new sfWidgetFormInputEmail(array(), array(
            'placeholder' => 'example@domain.com',
        ));
        $this->validatorSchema['email_address'] = new sfValidatorPass();

        $this->widgetSchema->setNameFormat('forgot_password[%s]');

        if (sfConfig::get('app_recaptcha_active', false)) {
            $this->setWidget('response', new sfWidgetFormInputHidden());
            $this->validatorSchema->setPostValidator(
                    new sfValidatorSchemaReCaptcha('challenge', 'response')
            );
            $this->validatorSchema->setOption('allow_extra_fields', true);
            $this->validatorSchema->setOption('filter_extra_fields', false);
        }

        parent::configure();
    }

    public function isValid()
    {
        $valid = parent::isValid();
        if ($valid) {
            $values = $this->getValues();
            $this->user = sfGuardUserTable::getInstance()->findOneBy('email_address', $values['email_address']);

            if ($this->user) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}