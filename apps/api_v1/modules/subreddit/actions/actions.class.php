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

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.', 401);
        return true;
    } 

    public function getUpdateValidators()
    {
        $validators = parent::getUpdateValidators();
        $validators['name'] = new sfValidatorString(array('required' => false));
        $validators['domain'] = new sfValidatorString(array('required' => false));
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
    
    public function validateCreate($payload, sfWebRequest $request = null)
    {
        parent::validateCreate($payload, $request);
        if (!$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "create new Subreddits.", 403);
    }

    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);
        if (!$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "delete Subreddits.", 403);
    }

    public function validateUpdate($payload, sfWebRequest $request = null)
    {
        parent::validateUpdate($payload, $request);
        $params = $this->parsePayload($payload);
        $primaryKey = $request->getParameter('id');
        if (!$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "alter Subreddits.", 403);
    }
}
