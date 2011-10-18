<?php

/**
 * subredditdeadline actions.
 *
 * @package    OpenMicNight
 * @subpackage subredditdeadline
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z xavier $
 * @see        class::autosubredditdeadlineActions
 */
class subredditdeadlineActions extends autosubredditdeadlineActions
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
