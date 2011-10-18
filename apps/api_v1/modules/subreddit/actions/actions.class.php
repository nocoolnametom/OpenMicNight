<?php

/**
 * subreddit actions.
 *
 * @package    OpenMicNight
 * @subpackage subreddit
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autosubredditActions
 */
class subredditActions extends autosubredditActions
{

    public function getUpdateValidators()
    {
        $validators = parent::getUpdateValidators();
        $validators['name'] = new sfValidatorString(array('required' => false));
        $validators['create_new_episodes_cron_formatted'] = new sfValidatorString(array(
                    'max_length' => 32,
                    'required' => false,
                ));
        $validators['episode_schedule_cron_formatted'] = new sfValidatorString(array(
                    'max_length' => 32,
                    'required' => false,
                ));
        return $validators;
    }

}
