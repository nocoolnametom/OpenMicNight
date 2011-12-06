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
    
    protected function doSave($params = array())
  {
    $this->object->save();

    // Set a Location header with the path to the new / updated object
    $this->getResponse()->setHttpHeader('Location', $this->getController()->genUrl(
      array_merge(array(
        'sf_route' => 'episode_show',
        'sf_format' => $this->getFormat(),
      ), $this->object->identifier(), $params)
    ));

    return sfView::NONE;
  }

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

    public function validateUpdate($payload, sfWebRequest $request = null)
    {
        parent::validateUpdate($payload, $request);

        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $primaryKey = $request->getParameter('id');
        $episode = EpisodeTable::getInstance()->find($primaryKey);

        if (!$this->getUser()->isSuperAdmin()) {
            $admin = sfGuardUserSubredditMembershipTable::getInstance()
                    ->getFirstByUserSubredditAndMemberships($user->getIncremented(), $episode->getSubredditId(), array('admin'));
            $moderator = sfGuardUserSubredditMembershipTable::getInstance()
                    ->getFirstByUserSubredditAndMemberships($user->getIncremented(), $episode->getSubredditId(), array('moderator'));
            if (!$admin) {
                if (array_key_exists('sf_guard_user_id', $params)
                        && $params['sf_guard_user_id'] != $user->getIncremented())
                    throw new sfException('You are not allowed to change the User of the Episode!', 403);
                if (array_key_exists('approved_by', $params)
                        && !$moderator
                        && $params['approved_by'] != $user->getIncremented())
                    throw new sfException('You are not allowed to add approval for the Episode!', 403);
            }
        }
    }

    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);
        if (!$this->getUser()->isSuperAdmin()) {
            throw new sfException('Your user does not have permissions to "
                    . "delete Episodes', 403);
        }
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
                ->from('Episode Episode')
                ->where('Episode.is_approved = ?', true)
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
                ->from('Episode Episode')
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
      $this->addToQuery($query, $params)->execute(array(), Doctrine::HYDRATE_ARRAY)
    )->getReturnValue();
  }

}
