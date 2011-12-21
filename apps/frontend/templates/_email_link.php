<?php
$website = null;
if ($sf_user->hasFlash('email_link'))
{
    $email = $sf_user->getFlash('email_link');
    preg_match('/@([^@]+)$/', $email, $matches);
    $domain = $matches[1];
    switch($domain)
    {
    // Google
    case 'gmail.com':
        $website = 'https://mail.google.com/';
        break;
    // Yahoo
    case 'yahoo.com':
    case 'yahoo.co.uk':
    case 'yahoo.co.in':
    case 'yahoo.ca':
        $website = 'https://login.yahoo.com/config/mail';
        break;
    // AOL
    case 'aol.com':
        $website = 'https://webmail.aol.com/';
        break;
    // Microsoft
    case 'hotmail.com':
    case 'live.com':
    case 'msn.com':
    case 'hotmail.co.uk':
        $website = 'http://login.live.com/';
        break;
    // Providers
    case 'verizon.net':
        $website= 'https://netmail.verizon.net/';
        break;
    case 'netzero.com':
        $website= 'https://webmail.netzero.net/';
        break;
    case 'comcast.net':
        $website= 'https://login.comcast.net/';
        break;
    case 'sbcglobal.net':
    case 'bellsouth.net':
        $website= 'https://sbc.yahoo.com/';
        break;
    case 'earthlink.net':
        $website= 'https://webmail.earthlink.net/';
        break;
    case 'cox.net':
        $website= 'https://webmail.cox.net/';
        break;
    case 'btinternet.net':
        $website= 'https://bt.yahoo.com/';
        break;
    case 'rediffmail.com':
        $website= 'http://login.rediff.com/cgi-bin/login.cgi';
        break;
    case 'charter.net':
        $website= 'https://web.charter.net/login/';
        break;
    case 'shaw.ca':
        $website= 'https://webmail.shaw.ca/';
        break;
    case 'ntlworld.com':
        $website= 'https://email.virginmedia.com/';
        break;
    }
}
if ($website): ?>
<div id="email_link"><?php echo link_to('Go check your inbox now!', $website); ?></div>
<?php endif; ?>