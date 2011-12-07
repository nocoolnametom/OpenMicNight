<?php

/**
 * sfGuardUserAdminForm for admin generators
 *
 * @package    sfDoctrineGuardPlugin
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfGuardUserAdminForm.class.php 23536 2009-11-02 21:41:21Z Kris.Wallsmith $
 */
class sfGuardUserAdminForm extends BasesfGuardUserAdminForm
{
  /**
   * @see sfForm
   */
  public function configure()
  {
      unset(
              $this['is_validated'],
              $this['reddit_validation_key'],
              $this['is_authorized'],
              $this['email_authorization_key'],
              $this['authorized_at'],
              $this['is_super_admin']
              );
      
      $this->widgetSchema['website'] = new sfWidgetFormInputUrl();
      $this->widgetSchema['email_address'] = new sfWidgetFormInputEmail();
  }
}
