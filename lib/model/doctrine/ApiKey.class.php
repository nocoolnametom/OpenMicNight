<?php

/**
 * ApiKey
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    OpenMicNight
 * @subpackage model
 * @author     Tom Doggett
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class ApiKey extends BaseApiKey
{

    /**
     * Request a new xAuth key for a user.
     *
     * @param string $email_address Email address of the user.
     * @param string $password      Password of the user.
     * @param int    $expires_in    How long the auth key should last. Max 1 year.
     * @return string|boolean       The auth key for this API.  False upon failure.
     */
    public function requestAuthKey($email_address, $password, $expires_in = 7200)
    {
        if (!$this->getIsActive())
            return false;
        // Attempt to find user by email address
        $user = sfGuardUserTable::getInstance()
                ->findOneByEmailAddress($email_address);
        /* @var $user sfGuardUser */
        if (!$user)
            throw new sfException('The user does not exist in the database.');
        // Find out how many failures in the past two minutes - max of five
        $failures = AuthFailureTable::getInstance()
                ->countFailuresMadeInRecentSeconds($this->getIncremented(),
                                                   $user->getIncremented(), 120);
        if ($failures >= 6)
            throw new sfException('Too many failures. Please wait a few minutes and try again.');
        if ($user && $user->checkPassword($password) && $user->getIsAuthorized() && $user->isActive()) {
            $year = 31536000;
            $expires_in = $expires_in >= $year ? $year : $expires_in;
            $user_auth = new sfGuardUserAuthKey();
            $user_auth->setSfGuardUser($user);
            $user_auth->setApiKey($this);
            $user_auth->setExpiresAt(date('Y-m-d H:i:s', time() + $expires_in));
            $auth_key = sha1(rand(0, 10000) . time());
            $user_auth->setAuthKey($auth_key);
            $user_auth->save();
            return $auth_key;
        }
        $failure = new AuthFailure();
        $failure->setSfGuardUser($user);
        $failure->setApiKey($this);
        $failure->save();
        return false;
    }

}
