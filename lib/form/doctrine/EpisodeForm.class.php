<?php

/**
 * Episode form.
 *
 * @package    OpenMicNight
 * @subpackage form
 * @author     Tom Doggett
 * @version    SVN: $Id: sfDoctrineFormTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class EpisodeForm extends BaseEpisodeForm
{

    public function configure()
    {
        unset($this['profile_id'], $this['subreddit_id'], $this['release_date'], $this['approved_by'], $this['is_approved'], $this['is_submitted'], $this['submitted_at']);
        
        $this->widgetSchema['graphic_file'] = new sfWidgetFormInputFileEditable(array(
                    'label' => 'Upload Graphic',
                    'file_src' => '',
                    'with_delete' => true,
                    'is_image' => true,
                    'edit_mode' => ($this->getObject()->getGraphicFile() != ""),
                ));

        $this->widgetSchema['audio_file'] = new sfWidgetFormInputFileEditable(array(
                    'label' => 'Upload Audio File',
                    'file_src' => '',
                    'with_delete' => true,
                    'is_image' => true,
                    'edit_mode' => ($this->getObject()->getAudioFile() != ""),
                ));

        $this->validatorSchema['graphic_file'] = new sfValidatorFile(array(
                    'max_size' => 500000,
                    'mime_types' => 'web_images',
                    'path' => 'uploads/graphics/',
                    'required' => false,
                ));
        $this->validatorSchema['graphic_file_delete'] = new sfValidatorPass();

        $this->validatorSchema['audio_file'] = new sfValidatorFile(array(
                    'max_size' => 500000,
                    'mime_types' => array(
                        'audio/basic',
                        'audio/mpeg',
                        'audio/wav',
                        'audio//x-aiff',
                        'audio/x-pn-realaudio',
                        'audio/x-wav',
                    ),
                    'path' => 'uploads/audio/staging/',
                    'required' => false,
                ));
        $this->validatorSchema['audio_file_delete'] = new sfValidatorPass();
    }

}
