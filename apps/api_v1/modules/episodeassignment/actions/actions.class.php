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
class episodeassignmentActions extends autoepisodeassignmentActions
{

    public function checkApiAuth($parameters, $content = null)
    {
        parent::checkApiAuth($parameters, $content);
        $this->getUser()->setParams($parameters);
        if (!$this->getUser()->apiIsAuthorized())
            throw new sfException('API authorization failed.', 401);
        return true;
    }

    public function validateCreate($payload, sfWebRequest $request = null)
    {
        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $admin = sfGuardUserSubredditMembershipTable::getInstance()
                ->getFirstByUserSubredditAndMemberships($user->getIncremented(),
                                                        $assignment->getEpisode()->getSubredditId(),
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
        return $this->doSave($params);
    }

}
