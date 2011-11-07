<?php /* @var $user sfGuardUser */ /* @var $episode Episode */  ?>
<p> Dear <?php echo $user->getFullName(); ?>,</p>

<p>You have been successfully paired with an empty episode slot!  Your episode
    will air on <?php echo $episode->getReleaseDate('l, F n, Y \a\t g:ia') ?>.
    Hurry and put something together and submit it for approval!  You need to
    ensure that your episode has been submitted and approval before
    <?php date('l, F n, Y \a\t g:ia', $deadline_date) ?>.  If you miss your
    deadline, your episode will not be aired, and someone else may take your
    spot.</p>

<p>If you'd rather not submit an episode, feel free to just wait it out and let
    your episode spot roll over to someone else.  There's no pressure on this;
    the only person in charge of this is you!</p>

<p>Have fun!</p>

<p>Sincerely,<br/> The <?php echo $app_name; ?> Team</p>