<?php

/**
 * episode actions.
 *
 * @package    OpenMicNight
 * @subpackage episode
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autoepisodeActions
 */
class episodeActions extends autoepisodeActions
{

    public function getCreateValidators()
    {
        // make created_at and updated_at fields non-required
        $validators = parent::getCreateValidators();
        $validators['created_at'] = new sfValidatorDateTime(array(
                    'required' => false,
                ));
        $validators['updated_at'] = new sfValidatorDateTime(array(
                    'required' => false,
                ));
        return $validators;
    }

    public function getUpdateValidators()
    {
        // make created_at and updated_at fields non-required
        $validators = parent::getUpdateValidators();
        $validators['subreddit_id'] = new sfValidatorDoctrineChoice(array(
                    'model' => Doctrine_Core::getTable('Episode')
                            ->getRelation('Subreddit')
                            ->getAlias(),
                    'required' => false,
                ));
        $validators['release_date'] = new sfValidatorDateTime(array(
                    'required' => false,
                ));
        return $validators;
    }
    
    public function getIndexValidators()
    {
        $validators = parent::getIndexValidators();
        
        return $validators;
    }

}
