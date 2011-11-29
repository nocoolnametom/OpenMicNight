<?php

/**
 * authortype actions.
 *
 * @package    OpenMicNight
 * @subpackage authortype
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autoauthortypeActions
 */
class authortypeActions extends autoauthortypeActions
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
        $validators['name'] = new sfValidatorString(array(
            'max_length' => 255,
            'required' => false,
            ));
        return $validators;
    }

    public function validateCreate($payload, sfWebRequest $request = null)
    {
        parent::validateCreate($payload, $request);
        if (!$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "create new AuthorTypes.", 403);
    }
    
    public function validateDelete($payload, sfWebRequest $request = null)
    {
        parent::validateDelete($payload, $request);
        
        $params = $this->parsePayload($payload);

        $user = $this->getUser()->getGuardUser();
        if (!$user)
            throw new sfException('Action requires an auth token.', 401);

        $primaryKey = $request->getParameter('id');
        $episode = EpisodeTable::getInstance()->find($primaryKey);

        if (!$this->getUser()->isSuperAdmin()) {
            throw new sfException('You are not allowed to delete AuthorTypes!', 403);
        }
    }

    public function validateUpdate($payload, sfWebRequest $request = null)
    {
        parent::validateUpdate($payload, $request);
        $params = $this->parsePayload($payload);
        $primaryKey = $request->getParameter('id');
        if (!$this->getUser()->isSuperAdmin())
            throw new sfException("Your user does not have permissions to "
                    . "alter AuthorTypes.", 403);
    }
    
}
