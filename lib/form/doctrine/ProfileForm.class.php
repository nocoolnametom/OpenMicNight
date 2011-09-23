<?php

/**
 * Profile form.
 *
 * @package    OpenMicNight
 * @subpackage form
 * @author     Tom Doggett
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class ProfileForm extends BaseProfileForm
{

    public function configure()
    {
        unset($this['is_super_admin'], $this['is_validated'], $this['last_logged_in'], $this['validation_key']);

        $this->widgetSchema['avatar'] = new sfWidgetFormInputFileEditable(array(
                    'label' => 'Upload Picture',
                    'file_src' => '',
                    'with_delete' => true,
                    'is_image' => true,
                    'edit_mode' => ($this->getObject()->getAvatar() != ""),
                ));
        $this->widgetSchema['state'] = new sfWidgetFormSelectUSState(array(
                    'add_empty' => true,
                ));
        $this->widgetSchema['country'] = new sfWidgetFormI18nChoiceCountry(array(
                    'culture' => 'en',
                    'add_empty' => true,
                ));

        $this->validatorSchema['email_address'] = new sfValidatorEmail();
        $this->validatorSchema['website'] = new sfValidatorUrl(array(
            'required' => false,
        ));
        $this->validatorSchema['avatar'] = new sfValidatorFile(array(
            'max_size' => 500000,
            'mime_types' => 'web_images',
            'path' => 'uploads/avatar/',
            'required' => false,
        ));
        $this->validatorSchema['avatar_delete'] = new sfValidatorPass();
    }

}
