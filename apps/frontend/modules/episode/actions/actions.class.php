<?php

require_once dirname(__FILE__).'/../lib/episodeGeneratorConfiguration.class.php';
require_once dirname(__FILE__).'/../lib/episodeGeneratorHelper.class.php';

/**
 * episode actions.
 *
 * @package    OpenMicNight
 * @subpackage episode
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class episodeActions extends autoEpisodeActions
{
    public function executeEdit(sfWebRequest $request)
    {
        parent::executeEdit($request);

        if ($this->episode->getGraphicFile()) {
            sfContext::getInstance()->getConfiguration()->loadHelpers("Asset");
            $this->form->getWidget('graphic_file')->setOption('file_src', image_path('/uploads/graphics/' . $this->episode->getGraphicFile()));
            $this->form->getWidget('graphic_file')->setLabel("Change Graphic");
        }
        $this->form->getValidator('graphic_file')->setOption('path', sfConfig::get('sf_web_dir') . '/uploads/graphics/');
        
        if ($this->episode->getAudioFile()) {
            sfContext::getInstance()->getConfiguration()->loadHelpers("Asset");
            $this->form->getWidget('audio_file')->setOption('file_src', image_path('/uploads/audio/staging/' . $this->episode->getAudioFile()));
            $this->form->getWidget('audio_file')->setLabel("Change Graphic");
        }
        $this->form->getValidator('audio_file')->setOption('path', sfConfig::get('sf_web_dir') . '/uploads/audio/staging/');
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->episode = $this->getRoute()->getObject();
        $this->form = $this->configuration->getForm($this->episode);

        if ($this->episode->getGraphicFile()) {
            sfContext::getInstance()->getConfiguration()->loadHelpers("Asset");
            $this->form->getWidget('graphic_file')->setOption('file_src', image_path('/uploads/graphics/' . $this->episode->getGraphicFile()));
            $this->form->getWidget('graphic_file')->setLabel("Change Graphic");
        }
        $this->form->getValidator('graphic_file')->setOption('path', sfConfig::get('sf_web_dir') . '/uploads/graphics/');
        
        if ($this->episode->getAudioFile()) {
            sfContext::getInstance()->getConfiguration()->loadHelpers("Asset");
            $this->form->getWidget('audio_file')->setOption('file_src', image_path('/uploads/audio/staging/' . $this->episode->getAudioFile()));
            $this->form->getWidget('audio_file')->setLabel("Change Graphic");
        }
        $this->form->getValidator('audio_file')->setOption('path', sfConfig::get('sf_web_dir') . '/uploads/audio/staging/');

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }
}
