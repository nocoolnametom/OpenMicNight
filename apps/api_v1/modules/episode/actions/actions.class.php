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

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.');
        return true;
    } 

    public function getUpdateValidators()
    {
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

}
