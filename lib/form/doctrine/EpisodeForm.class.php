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

        $this->widgetSchema['reddit_post_url'] = new sfWidgetFormInputUrl();
        $this->validatorSchema['reddit_post_url'] = new sfValidatorUrl(array(
                    'max_length' => 255,
                    'required' => false,
                ));
        $this->validatorSchema['graphic_file_delete'] = new sfValidatorPass();
        $this->validatorSchema['audio_file_delete'] = new sfValidatorPass();
    }

    public function processValues($values)
    {
        /* if ($values['audio_file'] instanceof sfValidatedFile) { // file was uploaded
          $basename = md5($values['audio_file']->getOriginalName() . rand(1111, 9999));
          $values['filename'] = $this->saveAudioFile($basename . '.jpg', 500, 500);
          $values['filename_thumb'] = $this->saveAudioFile($basename . '.thumb.jpg', 75, 75, true);
          }
          unset($values['file']); // do not bind the 'file' value to the object */
        return parent::processValues($values);
    }

    protected function saveAudioFile($filename)
    {
        // process $this->values['audio_file'] here
        return $filename;
    }

    protected function saveGraphicFile($filename)
    {
        // process $this->values['audio_file'] here
        return $filename;
    }

}
