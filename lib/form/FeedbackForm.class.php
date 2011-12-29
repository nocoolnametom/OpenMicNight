<?php

/**
 * Description of FeedbackForm
 *
 * @author doggetto
 */
class FeedbackForm extends BaseForm
{
    protected static $subjects;

    public function configure()
    {
        self::$subjects = sfConfig::get('app_feedback_subjects', array(
            'Subject A',
            'Subject B',
            'Subject C',
        ));
        
        $this->widgetSchema->setNameFormat('feedback[%s]');
        
        $this->setWidgets(array(
            'name'    => new sfWidgetFormInput(),
            'email'   => new sfWidgetFormInputEmail(),
            'subject' => new sfWidgetFormSelect(array('choices' => self::$subjects)),
            'message' => new sfWidgetFormTextarea(),
        ));
        $this->widgetSchema->setNameFormat('feedback[%s]');

        $this->setValidators(array(
            'name'    => new sfValidatorString(array('required' => false)),
            'email'   => new sfValidatorEmail(),
            'subject' => new sfValidatorChoice(array('choices' => array_keys(self::$subjects))),
            'message' => new sfValidatorString(array('min_length' => 4)),
        ));

        $this->widgetSchema->setLabels(array(
            'name'    => 'Your Name',
            'email'   => 'Your Email Address',
            'subject' => 'Subject',
            'message' => 'Your Message',
        ));
        
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
}