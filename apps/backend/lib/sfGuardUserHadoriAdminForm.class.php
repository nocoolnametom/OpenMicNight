<?php

/**
 * sfGuardUserHadoriAdminForm
 *
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: sfGuardFormSignin.class.php 23536 2009-11-02 21:41:21Z Kris.Wallsmith $
 */
class sfGuardUserHadoriAdminForm extends sfGuardUserAdminForm
{

    /**
     * @see sfForm
     */
    public function configure()
    {
        parent::configure();
        unset($this['groups_list'], $this['permissions_list']);
        $this->widgetSchema['password'] = new sfWidgetFormInputHidden(array(
                    'label' => ' ',
                ));
        $this->widgetSchema['password_again'] = new sfWidgetFormInputHidden(array(
                    'label' => ' ',
                ));
    }

}