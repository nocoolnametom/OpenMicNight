<?php

/**
 * email actions.
 *
 * @package    OpenMicNight
 * @subpackage email
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class emailActions extends sfActions
{

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $this->forward('default', 'module');
    }

    /**
     * Executes reg_initial action
     *
     * @param sfRequest $request A request object
     */
    public function executeReg_initial(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $this->forward404Unless($this->user);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();
    }

    /**
     * Executes reg_initial action
     *
     * @param sfRequest $request A request object
     */
    public function executeReg_reddit_post(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $this->forward404Unless($this->user);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();

        $this->reddit_post = sfConfig::get('app_email_reddit_validation_post_location');
        if (sfConfig::get('app_email_reddit_validation_local_file', false)) {
            $app_pattern = '/~([^~]+)~/';
            $app_location = preg_match($app_pattern, $this->reddit_post, $matches);
            $app_location = $app_location[1];
            $this->reddit_post = preg_replace($app_pattern, sfConfig::get($app_location), $this->reddit_post);
        }
    }

    /**
     * Executes reg_one_day action
     *
     * @param sfRequest $request A request object
     */
    public function executeReg_one_day(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $this->forward404Unless($this->user);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();
    }

    /**
     * Executes reg_one_week action
     *
     * @param sfRequest $request A request object
     */
    public function executeReg_one_week(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $this->forward404Unless($$this->user);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();
    }

    /**
     * Executes change_reddit_key action
     *
     * @param sfRequest $request A request object
     */
    public function executeChange_reddit_key(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $this->forward404Unless($this->user);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();
    }

    /**
     * Executes newly_opened_episode action
     *
     * @param sfRequest $request A request object
     */
    public function executeNewly_opened_episode(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $episode_id = $request->getParameter('episode_id');
        $this->episode = EpisodeTable::getInstance()->find($episode_id);
        $this->deadline_date = $request->getParameter('deadline');
        $this->forward404Unless($this->user && $this->episode && $this->deadline_date);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();
        $this->deadline_date = strtotime($this->deadline_date);
    }

    /**
     * Executes new_private_message action
     *
     * @param sfRequest $request A request object
     */
    public function executeNew_private_message(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $message_id = $request->getParameter('message_id');
        $this->message = MessageTable::getInstance()->find($message_id);
        $this->forward404Unless($this->user && $this->message);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();
        $this->sender = sfGuardUserTable::getInstance()->find($this->message->getSenderId());
    }

    /**
     * Executes episode_approval_pending action
     *
     * @param sfRequest $request A request object
     */
    public function executeEpisode_approval_pending(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $episode_id = $request->getParameter('episode_id');
        $this->episode = EpisodeTable::getInstance()->find($episode_id);
        $this->forward404Unless($this->user && $this->episode);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();
    }

    /**
     * Executes api_auth_request action
     *
     * @param sfRequest $request A request object
     */
    public function executeApi_auth_request(sfWebRequest $request)
    {
        $this->app_name = sfConfig::get('app_email_app_name');
        $user_id = $request->getParameter('user_id');
        $this->user = sfGuardUserTable::getInstance()->find($user_id);
        $api_id = $request->getParameter('api_id');
        $this->api = ApiKeyTable::getInstance()->find($api_id);
        $this->forward404Unless($this->user && $this->api);
        $this->name = $this->user->getPreferredName() ?
                $this->user->getPreferredName() : $this->user->getFullName();
    }

}
