<?php

/**
 * home actions.
 *
 * @package    OpenMicNight
 * @subpackage home
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class homeActions extends sfActions
{

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $this->user = $this->getUser()->getGuardUser();

        $this->subreddits = array();

        $this->episodes = $this->getIndexEpisodes();

        $subreddits = sfConfig::get('app_web_app_feed_default_subreddits');
        if (count($subreddits)) {
            $subreddit_data = Api::getInstance()->get('subreddit?domain='
                    . implode(',', $subreddits), true);
            $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
            foreach ($subreddits as $subreddit) {
                $this->subreddits[$subreddit->getIncremented()] = $subreddit;
            }
        }
    }

    public function executeFeedback(sfWebRequest $request)
    {
        if ($this->getUser()->getApiUserId()) {
            sfConfig::set('app_recaptcha_active', false);
        }

        $this->form = new FeedbackForm();

        if ($this->getUser()->getApiUserId()) {
            unset($this->form['name']);
            unset($this->form['email']);
            sfConfig::set('app_recaptcha_active', false);
        }
        
        $this->getUser()->setReferer($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());
    }

    public function executeSend(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod('post'));

        if ($this->getUser()->getApiUserId()) {
            sfConfig::set('app_recaptcha_active', false);
        }

        $this->form = new FeedbackForm();

        if ($this->getUser()->getApiUserId()) {
            unset($this->form['name']);
            unset($this->form['email']);
        }

        $requestData = $request->getParameter($this->form->getName());
        if (sfConfig::get('app_recaptcha_active', false)) {
            $requestData['challenge'] = $this->getRequestParameter('recaptcha_challenge_field');
            $requestData['response'] = $this->getRequestParameter('recaptcha_response_field');
        }
        $this->form->bind($requestData);
        if ($this->form->isValid()) {
            if ($this->getUser()->getApiUserId()) {
                $user_data = Api::getInstance()->get('user/'
                        . $this->getUser()->getApiUserId(), true);
                $user = ApiDoctrine::createQuickObject($user_data['body']);
            } else {
                $user = null;
            }

            $values = $this->form->getValues();

            ProjectConfiguration::registerZend();

            $name = $this->getUser()->getApiUserId() ? ($user->getPreferredName() ? $user->getPreferredName() : $user->getFullName()) : $this->form->getValue('name');
            $email = $this->getUser()->getApiUserId() ? $user->getEmailAddress() : $this->form->getValue('email');

            $signinUrl = $this->getUser()->getReferer($request->getReferer());
            
            $message = $name . ' ' . $email . "\n" . $values['message'] . "\nReferer:" . $signinUrl;
            $to = ProjectConfiguration::getApplicationEmailAddress();
            $subject = $values['subject'];
            
            AppMail::sendMail($to, $email, $subject, $message);

            $this->getUser()->setFlash('notice', 'Your message has been sent to ' . ProjectConfiguration::getApplicationName() . '.');
            return $this->redirect('' != $signinUrl ? $signinUrl : '@homepage');
        }
        $this->getUser()->setReferer($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());
        $this->setTemplate('feedback');
    }

    public function executeAboutus(sfWebRequest $request)
    {
        ;
    }
    
    public function executeApi(sfWebRequest $request)
    {
        ;
    }
    
    public function executeHowtohelp(sfWebRequest $request)
    {
        ;
    }
    
    public function executeHowtouse(sfWebRequest $request)
    {
        ;
    }
    
    public function executeRoadmap(sfWebRequest $request)
    {
        ;
    }
    
    public function executeBlog(sfWebRequest $request)
    {
        ;
    }

    protected function getIndexEpisodes()
    {
        $subreddit_ids = array();

        $subreddits = sfConfig::get('app_web_app_feed_default_subreddits');
        sfConfig::set('default_subreddits', $subreddits);
        if (count($subreddits)) {
            $subreddit_data = Api::getInstance()->get('subreddit?domain='
                    . implode(',', $subreddits), true);
            $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
            foreach ($subreddits as $subreddit) {
                if (!in_array($subreddit->getIncremented(), $subreddit_ids))
                    $subreddit_ids[] = $subreddit->getIncremented();
            }
        }

        $episodes = array();
        if (count($subreddit_ids)) {
            $episode_data = Api::getInstance()->get('episode/released?nwfw=&subreddit_id='
                    . implode(',', $subreddit_ids), true);
            $episodes = ApiDoctrine::createObjectArray('Episode', $episode_data['body']);
        }

        return $episodes;
    }
    

}
