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
            throw new sfException('API authorization failed.');
        return true;
    }

}
