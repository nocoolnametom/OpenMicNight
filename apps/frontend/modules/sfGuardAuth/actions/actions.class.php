<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(sfConfig::get('sf_plugins_dir') . '/sfDoctrineGuardPlugin/modules/sfGuardAuth/lib/BasesfGuardAuthActions.class.php');

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 23319 2009-10-25 12:22:23Z Kris.Wallsmith $
 */
class sfGuardAuthActions extends BasesfGuardAuthActions
{

    public function executeSignin($request)
    {
        $user = $this->getUser();
        if ($user->isAuthenticated()) {
            return $this->redirect('@homepage');
        }

        $class = sfConfig::get('app_sf_guard_plugin_signin_form', 'sfGuardFormSignin');
        $this->form = new $class();

        if ($request->isMethod('post')) {
            $this->form->bind($request->getParameter('signin'));
            if ($this->form->isValid()) {
                $values = $this->form->getValues();
                $this->getUser()->signin($values['user'], array_key_exists('remember', $values) ? $values['remember'] : false);

                // always redirect to a URL set in app.yml
                // or to the referer
                // or to the homepage
                $signinUrl = sfConfig::get('app_sf_guard_plugin_success_signin_url', $user->getReferer($request->getReferer()));

                return $this->redirect('' != $signinUrl ? $signinUrl : '@homepage');
            }
        } else {
            if ($request->isXmlHttpRequest()) {
                $this->getResponse()->setHeaderOnly(true);
                $this->getResponse()->setStatusCode(401);

                return sfView::NONE;
            }

            // if we have been forwarded, then the referer is the current URL
            // if not, this is the referer of the current request
            $user->setReferer($this->getContext()->getActionStack()->getSize() > 1 ? $request->getUri() : $request->getReferer());

            $module = sfConfig::get('sf_login_module');
            if ($this->getModuleName() != $module) {
                return $this->redirect($module . '/' . sfConfig::get('sf_login_action'));
            }

            $this->getResponse()->setStatusCode(401);
        }
    }

    public function executeVerify(sfWebRequest $request)
    {
        $key = $request->getParameter('key');
        $user = sfGuardUserTable::getInstance()->findOneBy('email_authorization_key', $key);
        $this->forward404Unless($key && $user);
        $user->setIsAuthorized(true);
        $user->setAuthorizedAt(date('Y-m-d H:i:s'));
        $user->save();
        $this->getUser()->setApiUserId($user->getIncremented());
        $this->getUser()->sendMail('RegisterRedditPost');
        $this->getUser()->setFlash('notice', 'Your email address has been validated!  Please log in!');
        $this->redirect('@sf_guard_signin');
    }

    public function executeForgot(sfWebRequest $request)
    {
        $this->form = new sfGuardRequestForgotPasswordForm();

        if ($request->isMethod('post')) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $user = $this->form->user;

                $new_password = substr(md5(time() . rand(0, 10000)), 0, 10);
                
                $user->setPassword($new_password);
                $user->save();

                $this->getUser()->setApiUserId($user->getIncremented());
                $this->getUser()->sendMail('EmailNewPassword', array(
                    'new_password' => $new_password,
                ));

                $this->getUser()->setFlash('notice', 'Check your e-mail! You should receive something shortly!');
                $this->redirect('@sf_guard_signin');
            } else {
                $this->getUser()->setFlash('error', 'Invalid e-mail address!');
            }
        }
    }

    public function executeRegister(sfWebRequest $request)
    {
        if ($this->getUser()->isAuthenticated()) {
            $this->getUser()->setFlash('notice', 'You are already registered and signed in!');
            $this->redirect('@homepage');
        }

        $this->form = new sfGuardRegisterForm();

        if ($request->isMethod('post')) {
            $this->form->bind($request->getParameter($this->form->getName()));
            if ($this->form->isValid()) {
                $user = $this->form->save();
                $this->getUser()->setApiUserId($user->getIncremented());
                $this->getUser()->sendMail('RegisterInitial');
                $this->getUser()->setFlash('notice', 'You have registered a user account.  Please check your email for further instructions.');
                $this->redirect('@homepage');
            }
        }
    }
    
    public function executeValidate(sfWebRequest $request)
    {
        $url = ValidationPostTable::getInstance()->getMostRecent()->getPostAddress();
        $this->redirect($url);
    }

}

