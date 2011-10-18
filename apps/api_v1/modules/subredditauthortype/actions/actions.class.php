<?php

/**
 * subredditauthortype actions.
 *
 * @package    OpenMicNight
 * @subpackage subredditauthortype
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autosubredditauthortypeActions
 */
class subredditauthortypeActions extends autosubredditauthortypeActions
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
