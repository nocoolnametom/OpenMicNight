<?php

/**
 * Description of FeedbackForm
 *
 * @author doggetto
 */
class FeedbackForm extends BaseForm
{
    protected static $subjects = array('Subject A', 'Subject B', 'Subject C');

    public function configure()
    {
        $this->setWidgets(array(
            'subject' => new sfWidgetFormSelect(array('choices' => self::$subjects)),
            'message' => new sfWidgetFormTextarea(),
        ));
        $this->widgetSchema->setNameFormat('feedback[%s]');

        $this->setValidators(array(
            'subject' => new sfValidatorChoice(array('choices' => array_keys(self::$subjects))),
            'message' => new sfValidatorString(array('min_length' => 4)),
        ));

        $this->widgetSchema->setLabels(array(
            'subject' => 'Subject',
            'message' => 'Your message',
        ));
    }
}