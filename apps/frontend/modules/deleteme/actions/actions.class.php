<?php

/**
 * deleteme actions.
 *
 * @package    OpenMicNight
 * @subpackage deleteme
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class deletemeActions extends sfActions
{

    public function executeIndex(sfWebRequest $request)
    {
        $this->episodes = Doctrine_Core::getTable('Episode')
                ->createQuery('a')
                ->execute();
    }

    public function executeNew(sfWebRequest $request)
    {
        $this->form = new EpisodeForm();
    }

    public function executeCreate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST));

        $this->form = new EpisodeForm();

        $this->processForm($request, $this->form);

        $this->setTemplate('new');
    }

    public function executeEdit(sfWebRequest $request)
    {
        $this->forward404Unless($episode = Doctrine_Core::getTable('Episode')->find(array($request->getParameter('id'))), sprintf('Object episode does not exist (%s).', $request->getParameter('id')));
        $this->form = new EpisodeForm($episode);
    }

    public function executeUpdate(sfWebRequest $request)
    {
        $this->forward404Unless($request->isMethod(sfRequest::POST) || $request->isMethod(sfRequest::PUT));
        $this->forward404Unless($episode = Doctrine_Core::getTable('Episode')->find(array($request->getParameter('id'))), sprintf('Object episode does not exist (%s).', $request->getParameter('id')));
        $this->form = new EpisodeForm($episode);

        $this->processForm($request, $this->form);

        $this->setTemplate('edit');
    }

    public function executeDelete(sfWebRequest $request)
    {
        $request->checkCSRFProtection();

        $this->forward404Unless($episode = Doctrine_Core::getTable('Episode')->find(array($request->getParameter('id'))), sprintf('Object episode does not exist (%s).', $request->getParameter('id')));
        $episode->delete();

        $this->redirect('deleteme/index');
    }

    protected function processForm(sfWebRequest $request, sfForm $form)
    {
        $form->bind($request->getParameter($form->getName()), $request->getFiles($form->getName()));
        if ($form->isValid()) {
            $episode = $form->save();

            $this->redirect('deleteme/edit?id=' . $episode->getId());
        }
    }

}
