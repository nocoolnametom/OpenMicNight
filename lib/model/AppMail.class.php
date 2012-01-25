<?php

class AppMail
{
    static protected $_to;
    static protected $_from;
    static protected $_subject;
    static protected $_message;
    static protected $_html_message;

    public static function sendMail($to, $from, $subject, $message,
                                    $html_message = null, $force_method = null)
    {
        self::$_to = $to;
        self::$_from = $from;
        self::$_subject = $subject;
        self::$_message = $message;
        self::$_html_message = $html_message;

        if ($force_method)
        {
            switch($force_method)
            {
            case 'zend':
                return self::sendUsingZendMail();
                break;
            case 'zendsmtp':
                return self::sendUsingZendMailSMTP();
                break;
            case 'amazon':
                return self::sendUsingAmazonSES();
                break;
            }
        }
        
        return self::sendUsingZendMail();
    }

    protected static function sendUsingZendMail($transport = null)
    {
        ProjectConfiguration::registerZend();
        $mail = new Zend_Mail();
        $mail->addHeader('X-MailGenerator',
                         ProjectConfiguration::getApplicationName());
        $mail->setBodyText(self::$_message);
        $mail->setBodyHtml(self::$_html_message);
        $mail->setFrom(self::$_from);
        if (is_array(self::$_to)) {
            foreach (self::$_to as $send_to) {
                $mail->addTo($send_to);
            }
        } else {
            $mail->addTo(self::$_to);
        }
        $mail->setSubject(self::$_subject);
        if (sfConfig::get('sf_environment') != 'prod') {
            if (sfConfig::get('sf_logging_enabled')) {
                sfContext::getInstance()->getLogger()->info('Mail sent: ' . $mail->getBodyText()->getRawContent());
            }
            return false;
        }
        return $mail->send($transport);
    }

    protected static function sendUsingZendMailSMTP()
    {
        ProjectConfiguration::registerZend();
        require_once 'Zend/Mail.php';

        $config = array(
            'auth' => 'login',
            'username' => ProjectConfiguration::getSMTPUser(),
            'password' => ProjectConfiguration::getSMTPPassword(),
        );

        if (ProjectConfiguration::getSMTPUseTLS())
            $config['ssl'] = 'tls';

        $transport = new Zend_Mail_Transport_Smtp(ProjectConfiguration::getSMTPServer(), $config);

        return self::sendUsingZendMail($transport);
    }

    protected static function sendUsingAmazonSES()
    {
        ProjectConfiguration::registerAws();

        $email = new AmazonSES();

        $message = array(
            'Subject.Data' => self::$_subject,
            'Body.Text.Data' => self::$_message,
        );
        $opt = array(
            'ReplyToAddresses' => self::$_from
        );

        if (self::$_html_message)
            $config['Body.Html.Data'] = '';

        $response = $email->send_email(
                self::$_from, array('ToAddresses' => $to), $message, $opt
        );

        return $response->isOK();
    }
}