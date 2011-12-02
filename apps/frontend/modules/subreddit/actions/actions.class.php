<?php

/**
 * subreddit actions.
 *
 * @package    OpenMicNight
 * @subpackage subreddit
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class subredditActions extends sfActions
{

    protected function getSubredditId(sfWebRequest $request)
    {
        $this->forward404Unless($request->getParameter('id') || $request->getParameter('domain'));
        if ($request->getParameter('domain')) {
            $auth_key = $this->getUser()->getApiAuthKey();
            $domain = $request->getParameter('domain');
            $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit?domain=' . urlencode($domain), true);
            $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
            $this->forward404Unless(count($subreddits) && $subreddits[0]->getIncremented());
            $subreddit_id = $subreddits[0]->getId();
        } else {
            $subreddit_id = $request->getParameter('id');
        }
        return $subreddit_id;
    }

    public function executeIndex(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit', true);
        $this->subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
    }

    public function executeEpisodes(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_id = $this->getSubredditId($request);
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/released?subreddit_id=' . $subreddit_id, true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);
    }

    public function executeSignup(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_id = $this->getSubredditId($request);
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $subreddit_id, true);
        $this->subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/future?subreddit_id=' . $subreddit_id, true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);
        $deadline_data = Api::getInstance()->setUser($auth_key)->get('subredditdeadline?subreddit_id=' . $subreddit_id, true);
        $this->deadlines = ApiDoctrine::createQuickObjectArray($deadline_data['body']);

        //Cheating here... Need to fix to an API call...
        $query = Doctrine_Query::create()
                        ->select('EpisodeAssignment.author_type_id, EpisodeAssignment.episode_id')
                        ->from('EpisodeAssignment EpisodeAssignment')
                        ->leftJoin('Episode Episode')
                        ->where('EpisodeAssignment.sf_guard_user_id = ?', $this->getUser()->getApiUserId())
                        ->andWhere('Episode.subreddit_id = ? ', $subreddit_id)
                        ->andWhere('Episode.release_date > ?', date('Y-m-d H:i:s'))
                        ->fetchArray();
        $this->assigned_author_types = array();
        $this->assigned_episodes = array();
        foreach($query as $entry)
        {
            $this->assigned_author_types[] = (string)$entry['author_type_id'];
            $this->assigned_episodes[] = (string)$entry['episode_id'];
        }
    }

    public function executeNew(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->form = new SubredditForm();
    }

    public function executeCreate(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->forward404Unless($request->isMethod(sfRequest::POST));

        $this->form = new SubredditForm();

        $this->processForm($request, $this->form);

        $this->setTemplate('new');
    }

    public function executeEdit(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'), true);
        $subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        $this->form = new SubredditForm($subreddit);
    }

    public function executeShow(sfWebRequest $request)
    {
        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_id = $this->getSubredditId($request);
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $subreddit_id, true);
        $this->subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $this->forward404Unless($this->subreddit && $this->subreddit->getId());
        $episodes_data = Api::getInstance()->setUser($auth_key)->get('episode/released?subreddit_id=' . $subreddit_id, true);
        $this->episodes = ApiDoctrine::createQuickObjectArray($episodes_data['body']);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));

        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'), true);
        $subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        $this->form = new SubredditForm($subreddit);

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeDelete(sfWebRequest $request)
    {
        $this->forward404Unless($this->getUser()->isAuthenticated());
        $request->checkCSRFProtection();

        $auth_key = $this->getUser()->getApiAuthKey();
        $subreddit_data = Api::getInstance()->setUser($auth_key)->get('subreddit/' . $request->getParameter('id'), true);
        $subreddit = ApiDoctrine::createObject('Subreddit', $subreddit_data['body']);
        $this->forward404Unless($subreddit && $subreddit->getId());

        //$subreddit->delete();
        $result = Api::getInstance()->setUser($auth_key)->delete('subreddit/' . $subreddit->getId(), true);
        $this->checkHttpCode($result);

        $this->redirect('subreddit/index');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $auth_key = $this->getUser()->getApiAuthKey();
            if ($form->getValue('id')) {
                // Update existing item.
                $values = $form->getObject()->getModified();
                $subreddit = $form->getObject();
                unset($values['id']);
                $id = $form->getValue('id');
                $result = Api::getInstance()->setUser($auth_key)->put('subreddit/' . $id, $values);
                $this->checkHttpCode($result);
                $test_subreddit = ApiDoctrine::createObject('Subreddit', $result['body']);
                $subreddit = $test_subreddit ? $test_subreddit : $subreddit;
            } else {
                // Create new item
                $values = $form->getValues();
                $subreddit = $form->getObject();
                foreach ($values as $key => $value) {
                    if (is_null($value))
                        unset($values[$key]);
                }
                $result = Api::getInstance()->setUser($auth_key)->post('subreddit', $values);
                $this->checkHttpCode($result);
                $test_subreddit = ApiDoctrine::createObject('Subreddit', $result['body']);
                $subreddit = $test_subreddit ? $test_subreddit : $subreddit;
                if (is_null($subreddit->getIncremented()))
                    $this->redirect('subreddit');
            }

            $this->redirect('subreddit/edit?id=' . $subreddit->getId());
        }
    }

    protected function checkHttpCode($result)
    {
        $http_code = $result['headers']['http_code'];
        if ($http_code != 200) {
            $message = array_key_exists('message', $result['body']) ? $result['body']['message'] : 'An error occured.';
            $message = array_key_exists(0, $result['body']) && array_key_exists('message', $result['body'][0]) ? $result['body'][0]['message'] : $message;
            $this->getUser()->setFlash('error', "($http_code) $message");
        } else {
            $this->getUser()->setFlash('notice', 'Action was completed successfully.');
        }
    }

}
