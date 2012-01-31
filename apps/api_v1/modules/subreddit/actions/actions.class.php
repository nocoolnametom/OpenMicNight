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

    //$domains = 'atheism,christianity,exmormon,funny,herdditnet,lds,loseit,mormon,music,mylittlepony,science,technology,todayilearned,twoxchromosomes';
    
    public function executeDefaultfeed(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::GET));
        $params = $request->getParameterHolder()->getAll();

        // notify an event before the action's body starts
        $this->dispatcher->notify(new sfEvent($this, 'sfDoctrineRestGenerator.get.pre', array('params' => $params)));

        $request->setRequestFormat('html');
        $this->setTemplate('index');
        $params = $this->cleanupParameters($params);

        try {
            $format = $this->getFormat();
            //$this->validateApiAuth($request->getParameterHolder()->getAll());
            $this->validateIndex($params, $request);
        } catch (Exception $e) {
            $this->getResponse()->setStatusCode($e->getCode() ? $e->getCode() : 406);
            $serializer = $this->getSerializer();
            $this->getResponse()->setContentType($serializer->getContentType());
            $error = $e->getMessage();

            // event filter to enable customisation of the error message.
            $result = $this->dispatcher->filter(
                            new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'),
                            $error
                    )->getReturnValue();

            if ($error === $result) {
                $error = array(array('message' => $error));
                $this->output = $serializer->serialize($error, 'error');
            } else {
                $this->output = $serializer->serialize($result);
            }

            return sfView::SUCCESS;
        }

        $domains = sfConfig::get('app_web_app_feed_default_subreddits', array());
        
        $q = Doctrine_Query::create()
                ->from('Subreddit Subreddit')
                ->andWhereIn('Subreddit.domain', $domains);

        $this->customQueryExecute($q, $params);
        $isset_pk = (!isset($isset_pk) || $isset_pk) && isset($params['id']);
        if ($isset_pk && count($this->objects) == 0) {
            $request->setRequestFormat($format);
            $this->forward404();
        }


        // configure the fields of the returned objects and eventually hide some
        $this->setFieldVisibilityIndex();
        $this->configureFields();

        $serializer = $this->getSerializer();
        $this->getResponse()->setContentType($serializer->getContentType());
        $this->output = $serializer->serialize($this->objects, $this->model);
        unset($this->objects);
    }

    /**
     * Allows for the upload of a Subreddit intro sound file.
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeUpload_intro(sfWebRequest $request)
    {
        // PUT makes more sense, but I am limited currently by my API to POST.

        $this->forward404Unless($request->isMethod(sfRequest::POST));
        $content = $request->getContent();

        // Restores backward compatibility. Content can be the HTTP request full body, or a form encoded "content" var.
        if (strpos($content, 'content=') === 0 || $request->hasParameter('content')) {
            $content = $request->getParameter('content');
        }

        $request->setRequestFormat('html');

        try {
            $parameters = $request->getParameterHolder()->getAll();
            $params = $this->getApiAuthFieldValues($parameters, $content);
            $this->validateUpload($content, $request);
        } catch (Exception $e) {
            $this->getResponse()->setStatusCode($e->getCode() ? $e->getCode() : 406);
            $serializer = $this->getSerializer();
            $this->getResponse()->setContentType($serializer->getContentType());
            $error = $e->getMessage();

            // event filter to enable customisation of the error message.
            $result = $this->dispatcher->filter(
                            new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'),
                            $error
                    )->getReturnValue();

            if ($error === $result) {
                $error = array(array('message' => $error));
                $this->output = $serializer->serialize($error, 'error');
            } else {
                $this->output = $serializer->serialize($result);
            }

            $this->setTemplate('index');
            return sfView::SUCCESS;
        }

        // We move the file from its temporary location to the Episode in question.
        if ($this->_nice_filename && $this->_temporary_file_location) {
            $targetDir = rtrim(ProjectConfiguration::getSubredditAudioFileLocalDirectory(),
                               '/');
            $pattern = '/\.([^\.]+)$/';
            preg_match($pattern, $filename, $matches);
            $extension = (array_key_exists(1, $matches) ? $matches[1] : 'mp3');

            // We don't need the upload hash because we're not uploading AJAX-like in real time.
            $hash = sha1(microtime() . $this->object->getIncremented());
            $fileName = $hash . '.' . $extension;

            //Move the file.
            rename($this->_temporary_file_location, $targetDir . '/' . $fileName);

            // update and save it
            $this->object->setEpisodeIntro($fileName);
        }

        return $this->doSave($params);
    }

    /**
     * Allows for the upload of a Subreddit intro sound file.
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeUpload_outro(sfWebRequest $request)
    {
        // PUT makes more sense, but I am limited currently by my API to POST.

        $this->forward404Unless($request->isMethod(sfRequest::POST));
        $content = $request->getContent();

        // Restores backward compatibility. Content can be the HTTP request full body, or a form encoded "content" var.
        if (strpos($content, 'content=') === 0 || $request->hasParameter('content')) {
            $content = $request->getParameter('content');
        }

        $request->setRequestFormat('html');

        try {
            $parameters = $request->getParameterHolder()->getAll();
            $params = $this->getApiAuthFieldValues($parameters, $content);
            $this->validateUpload($content, $request);
        } catch (Exception $e) {
            $this->getResponse()->setStatusCode($e->getCode() ? $e->getCode() : 406);
            $serializer = $this->getSerializer();
            $this->getResponse()->setContentType($serializer->getContentType());
            $error = $e->getMessage();

            // event filter to enable customisation of the error message.
            $result = $this->dispatcher->filter(
                            new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'),
                            $error
                    )->getReturnValue();

            if ($error === $result) {
                $error = array(array('message' => $error));
                $this->output = $serializer->serialize($error, 'error');
            } else {
                $this->output = $serializer->serialize($result);
            }

            $this->setTemplate('index');
            return sfView::SUCCESS;
        }

        // We move the file from its temporary location to the Episode in question.
        if ($this->_nice_filename && $this->_temporary_file_location) {
            $targetDir = rtrim(ProjectConfiguration::getSubredditAudioFileLocalDirectory(),
                               '/');
            $pattern = '/\.([^\.]+)$/';
            preg_match($pattern, $filename, $matches);
            $extension = (array_key_exists(1, $matches) ? $matches[1] : 'mp3');

            // We don't need the upload hash because we're not uploading AJAX-like in real time.
            $hash = sha1(microtime() . $this->object->getIncremented());
            $fileName = $hash . '.' . $extension;

            //Move the file.
            rename($this->_temporary_file_location, $targetDir . '/' . $fileName);

            // update and save it
            $this->object->setEpisodeOutro($fileName);
        }

        return $this->doSave($params);
    }

    public function validateCreate($payload, sfWebRequest $request = null)
    {
        parent::validateCreate($payload, $request);
        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);
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
        $user = $this->getUser()->getGuardUser();
        $primaryKey = $request->getParameter('id');
        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $primaryKey,
                                                        array('admin'));
        if (!$this->getUser()->isSuperAdmin() && !$admin)
            throw new sfException("Your user does not have permissions to "
                    . "alter Subreddits.", 403);
    }

    public function validateUpload($payload, sfWebRequest $request = null)
    {
        if (!$request->hasParameter('id'))
            throw new sfException('No subreddit given.', 400);
        $this->object = SurbedditTable::getInstance()->find($request->hasParameter('id'));
        if (!$this->object)
            throw new sfException('Cannot find subreddit.', 404);

        $content_file = $request->getFiles('filename');
        $this->_temporary_file_location = array_key_exists('tmp_name',
                                                           $content_file) ? $content_file['tmp_name']
                    : null;
        $this->_nice_filename = array_key_exists('name', $content_file) ? $content_file['name']
                    : null;

        /* Check that the current user is an admin of the Subreddit or otherwise
         * has permission to upload. */
        $membership_data = Api::getInstance()->setUser($auth_key)->get('subredditmembership?'
                . 'sf_guard_user_id=' . $this->getUser()->getGuardUser()->getIncremented()
                . '&subreddit_id=' . $this->object->getIncremented(), true);
        $membership = is_array($membership_data['body']) && array_key_exists(0,
                                                                             $membership_data['body'])
                    ? ApiDoctrine::createQuickObject($membership_data['body'][0])
                    : null;
        $valid_admin = (bool) ($membership
                && in_array($membership->getMembership()->getType(),
                            array(
                    'admin',
                )));
        if (!$this->getUser()->isSuperAdmin() && !$valid_admin) {
            throw new sfException('Your user does not have permissions to '
                    . 'upload audio for this Subreddit.', 403);
        }
    }
    
    /**
     * Create the query for selecting objects, eventually along with related
     * objects
     *
     * @param   array   $params  an array of criterions for the selection
     */
    public function addToQuery(Doctrine_Query $query, $params)
    {
        if (isset($sort)) {
            $query->orderBy($sort);
        }

        if (isset($params['id'])) {
            $values = explode(',', $params['id']);

            if (count($values) == 1) {
                $query->andWhere($this->model . '.id = ?', $values[0]);
            } else {
                $query->whereIn($this->model . '.id', $values);
            }

            unset($params['id']);
        }

        foreach ($params as $name => $value) {
            $query->andWhere($this->model . '.' . $name . ' = ?', $value);
        }

        return $query;
    }

    /**
     * Execute the query for selecting a collection of objects, eventually
     * along with related objects
     *
     * @param   array   $params  an array of criterions for the selection
     */
    public function customQueryExecute(Doctrine_Query $query, $params)
    {
        $this->objects = $this->dispatcher->filter(
                        new sfEvent(
                                $this,
                                'sfDoctrineRestGenerator.filter_results',
                                array()
                        ),
                        $this->addToQuery($query, $params)->execute(array(),
                                                                    Doctrine::HYDRATE_ARRAY)
                )->getReturnValue();
    }
}
