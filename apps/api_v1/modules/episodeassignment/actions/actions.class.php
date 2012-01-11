<?php

/**
 * episodeassignment actions.
 *
 * @package    OpenMicNight
 * @subpackage episodeassignment
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autoepisodeassignmentActions
 */
class episodeassignmentActions extends autoEpisodeassignmentActions
{

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.', 401);
        return true;
    }
    
    public function getCreateValidators()
    {
        $validators = parent::getCreateValidators();
        $validators['id_hash'] = new sfValidatorString(array(
                    'required' => false,
                ));
        return $validators;
    }

    public function validateCreate($payload, sfWebRequest $request = null)
    {
        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $episode = EpisodeTable::getInstance()->find($params['episode_id']);

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $episode->getSubredditId(),
                                                        array('admin'));

        if (array_key_exists('sf_guard_user_id', $params)
                && !$this->getUser()->isSuperAdmin()
                && !$admin
                && $params['sf_guard_user_id'] != $user->getIncremented())
            throw new sfException('You are not allowed to create an EpisodeAssignment for someone else.', 403);

        if (!array_key_exists('sf_guard_user_id', $params))
            $params[] = $user->getIncremented();

        $payload = $this->packagePayload($params);
        unset($this->_payload_array);

        parent::validateCreate($payload, $request);
    }

    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);
        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $primaryKey = $request->getParameter('id');
        $assignment = EpisodeAssignmentTable::getInstance()->find($primaryKey);

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $assignment->getEpisode()->getSubredditId(),
                                                        array('admin'));

        if (!$this->getUser()->isSuperAdmin()
                && !$admin
                && $assignment->getSfGuardUserId() != $user->getIncremented())
            throw new sfException('You are not allowed to delete this EpisodeAssignment.', 403);
    }

    public function validateUpdate($payload, sfWebRequest $request = null)
    {
        parent::validateUpdate($payload, $request);
        $params = $this->parsePayload($payload);
        $primaryKey = $request->getParameter('id');
        $assignment = EpisodeAssignmentTable::getInstance()->find($primaryKey);
        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $assignment->getEpisode()->getSubredditId(),
                                                        array('admin'));
        if (array_key_exists('sf_guard_user_id', $params)
                && !$this->getUser()->isSuperAdmin()
                && !$admin)
            throw new sfException('You are not allowed to change users for this EpisodeAssignment.', 403);
    }

    /**
     * Creates a EpisodeAssignment object
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeCreate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST));
        $content = $request->getContent();

        // Restores backward compatibility. Content can be the HTTP request full body, or a form encoded "content" var.
        if (strpos($content, 'content=') === 0) {
            $content = $request->getParameter('content');
        }
        if ($content === false) {
            $content = $request->getPostParameter('content'); // Last chance to get the content!
        }

        $request->setRequestFormat('html');

        try {
            $parameters = $request->getParameterHolder()->getAll();
            $params = $this->getApiAuthFieldValues($parameters, $content);
            $this->validateApiAuth($parameters, $content);
            $this->validateCreate($content, $request);
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

        $object_params = $this->parsePayload($content);
        if (!array_key_exists('sf_guard_user_id', $object_params))
            $object_params['sf_guard_user_id'] = $this->getUser()->getGuardUser()->getIncremented();

        $this->object = $this->createObject();
        $this->object->importFrom('array', $object_params);
        
        try {
            $return = $this->doSave($params);
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
        return $return;
    }

    /**
     * Retrieves a  collection of Episode objects
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeFuture(sfWebRequest $request)
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
                            new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'), $error
                    )->getReturnValue();

            if ($error === $result) {
                $error = array(array('message' => $error));
                $this->output = $serializer->serialize($error, 'error');
            } else {
                $this->output = $serializer->serialize($result);
            }

            return sfView::SUCCESS;
        }

        $q = Doctrine_Query::create()
                ->from('EpisodeAssignment EpisodeAssignment')
                ->leftJoin('EpisodeAssignment.Episode Episode')
                ->leftJoin('Deadline ON (Deadline.author_type_id = EpisodeAssignment.author_type_id)')
                ->where('Episode.id = EpisodeAssignment.episode_id')
                ->andWhere('EpisodeAssignment.missed_deadline <> 1');
        if (array_key_exists('sf_guard_user_id', $params))
           $q =  $q->andWhere('EpisodeAssignment.sf_guard_user_id = ?', $params['sf_guard_user_id']);
        $q =  $q->andWhere('Episode.release_date > ?', date('Y-m-d H:i:s'))
                ->andWhere('EpisodeAssignment.id <> Episode.episode_assignment_id')
                ->orWhere('Episode.episode_assignment_id IS NULL');

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
     * Retrieves a  collection of Episode objects
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeCurrent(sfWebRequest $request)
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
                            new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'), $error
                    )->getReturnValue();

            if ($error === $result) {
                $error = array(array('message' => $error));
                $this->output = $serializer->serialize($error, 'error');
            } else {
                $this->output = $serializer->serialize($result);
            }

            return sfView::SUCCESS;
        }

        $q = Doctrine_Query::create()
                ->from('EpisodeAssignment EpisodeAssignment')
                ->leftJoin('EpisodeAssignment.Episode Episode')
                ->where('Episode.id = EpisodeAssignment.episode_id')
                ->andWhere('EpisodeAssignment.missed_deadline <> 1');
        if (array_key_exists('sf_guard_user_id', $params))
           $q =  $q->andWhere('EpisodeAssignment.sf_guard_user_id = ?', $params['sf_guard_user_id']);
        $q =  $q->andWhere('Episode.episode_assignment_id = EpisodeAssignment.id')
                ->andWhere('Episode.release_date > ?', date('Y-m-d H:i:s'));

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
     * Retrieves a  collection of Episode objects
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeReleased(sfWebRequest $request)
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
                            new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'), $error
                    )->getReturnValue();

            if ($error === $result) {
                $error = array(array('message' => $error));
                $this->output = $serializer->serialize($error, 'error');
            } else {
                $this->output = $serializer->serialize($result);
            }

            return sfView::SUCCESS;
        }

        $q = Doctrine_Query::create()
                ->from('EpisodeAssignment EpisodeAssignment')
                ->leftJoin('EpisodeAssignment.Episode Episode')
                ->where('Episode.id = EpisodeAssignment.episode_id')
                ->andWhere('EpisodeAssignment.missed_deadline <> 1');
        if (array_key_exists('sf_guard_user_id', $params))
           $q =  $q->andWhere('EpisodeAssignment.sf_guard_user_id = ?', $params['sf_guard_user_id']);
        $q =  $q->andWhere('Episode.episode_assignment_id IS NOT NULL')
                ->andWhere('Episode.is_approved = ?', true)
                ->andWhere('Episode.release_date <= ?', date('Y-m-d H:i:s'));
        
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
     * Creates a token referring to an sfGuardUser object
     * @param   sfWebRequest   $request a request object
     * @return  string
     */
    public function executeValidhash(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::GET));
        $params = $request->getParameterHolder()->getAll();

        // notify an event before the action's body starts
        $this->dispatcher->notify(new sfEvent($this, 'sfDoctrineRestGenerator.get.pre', array('params' => $params)));

        $request->setRequestFormat('html');
        $this->setTemplate('index');
        $params = $this->cleanupParameters($params);
        
        $is_valid = false;

        try {
            $format = $this->getFormat();
            if (!array_key_exists('subreddit_id', $params) && !array_key_exists('id_hash', $params))
                throw new sfException('Missing reference to subreddit_id and id_hash!', 400);
            if (!array_key_exists('subreddit_id', $params))
                throw new sfException('Missing reference to subreddit_id!', 400);
            if (!array_key_exists('id_hash', $params))
                throw new sfException('Missing reference to id_hash!', 400);
            $check = EpisodeAssignmentTable::getInstance()->getByIdHash($params['id_hash'], $params['subreddit_id']);
            if ($check)
                $is_valid = true;
        } catch (Exception $e) {
            $this->getResponse()->setStatusCode($e->getCode() ? $e->getCode() : 406);
            $serializer = $this->getSerializer();
            $this->getResponse()->setContentType($serializer->getContentType());
            $error = $e->getMessage();

            // event filter to enable customisation of the error message.
            $result = $this->dispatcher->filter(
                            new sfEvent($this, 'sfDoctrineRestGenerator.filter_error_output'), $error
                    )->getReturnValue();

            if ($error === $result) {
                $error = array(array('message' => $error));
                $this->output = $serializer->serialize($error, 'error');
            } else {
                $this->output = $serializer->serialize($result);
            }

            return sfView::SUCCESS;
        }

        $serializer = $this->getSerializer();
        $this->getResponse()->setContentType($serializer->getContentType());
        $this->output = $serializer->serialize(array(
            'is_valid' => $is_valid,
                ), $this->model, false);
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
