<?php

/**
 * BasesfGuardUserAdminForm
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: BasesfGuardUserAdminForm.class.php 25546 2009-12-17 23:27:55Z Jonathan.Wage $
 */
class BasesfGuardUserAdminForm extends BasesfGuardUserForm
{
  /**
   * @see sfForm
   */
  public function setup()
  {
    parent::setup();
    
    $this->widgetSchema['email_address'] = new sfWidgetFormInputEmail(array(), array(
        'placeholder' => 'example@domain.com',
        ));
    $this->widgetSchema['username'] = new sfWidgetFormInputText(array(), array(
        'placeholder' => 'nocoolnametom',
        ));
    $this->widgetSchema['password'] = new sfWidgetFormInputPassword();
    $this->widgetSchema['full_name'] = new sfWidgetFormInputText(array(), array(
        'placeholder' => 'First and last name',
        ));
    $this->widgetSchema['preferred_name'] = new sfWidgetFormInputText(array(), array(
        'placeholder' => 'Bob?',
        ));
    $this->widgetSchema['website'] = new sfWidgetFormInputUrl(array(), array(
        'placeholder' => 'http://nocoolnametom.com/',
        ));
    $this->widgetSchema['twitter_account'] = new sfWidgetFormInputText(array(), array(
        'placeholder' => '@NoCoolName_Tom',
        ));
    $this->widgetSchema['avatar'] = new sfWidgetFormInputUrl();
    $this->widgetSchema['short_bio'] = new sfWidgetFormTextarea(array(), array(
        'placeholder' => 'A bit about yourself...',
        ));
    $this->widgetSchema['address_line_one'] = new sfWidgetFormInputText();
    $this->widgetSchema['address_line_two'] = new sfWidgetFormInputText();
    $this->widgetSchema['city'] = new sfWidgetFormInputText();
    $this->widgetSchema['state'] = new sfWidgetFormSelectUSState();
    $this->widgetSchema['zip_code'] = new sfWidgetFormInputText();
    $this->widgetSchema['country'] = new sfWidgetFormI18nChoiceCountry(array(
        'add_empty' => true,
        ));
    $this->widgetSchema['preferred_language'] = new sfWidgetFormI18nChoiceLanguage();
    
    $this->validatorSchema['country'] = new sfValidatorI18nChoiceCountry();
    $this->validatorSchema['preferred_language'] = new sfValidatorI18nChoiceLanguage();

    unset(
      $this['last_login'],
      $this['created_at'],
      $this['updated_at'],
      $this['salt'],
      $this['algorithm']
    );

    $this->widgetSchema['groups_list']->setLabel('Groups');
    $this->widgetSchema['permissions_list']->setLabel('Permissions');

    $this->widgetSchema['password'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password']->setOption('required', false);
    $this->widgetSchema['password_again'] = new sfWidgetFormInputPassword();
    $this->validatorSchema['password_again'] = clone $this->validatorSchema['password'];

    $this->widgetSchema->moveField('password_again', 'after', 'password');

    $this->mergePostValidator(new sfValidatorSchemaCompare('password', sfValidatorSchemaCompare::EQUAL, 'password_again', array(), array('invalid' => 'The two passwords must be the same.')));
  }
}