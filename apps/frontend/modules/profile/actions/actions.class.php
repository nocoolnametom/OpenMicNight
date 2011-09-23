<?php

require_once dirname(__FILE__) . '/../lib/profileGeneratorConfiguration.class.php';
require_once dirname(__FILE__) . '/../lib/profileGeneratorHelper.class.php';

/**
 * profile actions.
 *
 * @package    OpenMicNight
 * @subpackage profile
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class profileActions extends autoProfileActions
{

    public function executeShow(sfWebRequest $request)
    {
        $this->profile = $this->getRoute()->getObject();
    }

    public function executeEdit(sfWebRequest $request)
    {
        parent::executeEdit($request);

        if ($this->profile->getAvatar()) {
            sfContext::getInstance()->getConfiguration()->loadHelpers("Asset");
            $this->form->getWidget('avatar')->setOption('file_src', image_path('/uploads/avatar/' . $this->profile->getAvatar()));
            $this->form->getWidget('avatar')->setLabel("Change Picture");
        }
        $this->form->getValidator('avatar')->setOption('path', sfConfig::get('sf_web_dir') . '/uploads/avatar/');
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->profile = $this->getRoute()->getObject();
        $this->form = $this->configuration->getForm($this->profile);

        if ($this->profile->getAvatar()) {
            sfContext::getInstance()->getConfiguration()->loadHelpers("Asset");
            $this->form->getWidget('avatar')->setOption('file_src', image_path('/uploads/avatar/' . $this->profile->getAvatar()));
            $this->form->getWidget('avatar')->setLabel("Change Picture");
        }
        $this->form->getValidator('avatar')->setOption('path', sfConfig::get('sf_web_dir') . '/uploads/avatar/');

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

}
