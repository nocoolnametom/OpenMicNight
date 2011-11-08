<?php /* @var $user sfGuardUser */ /* @var $api ApiKey */  ?>
<p> Dear <?php echo $name ?>,</p>

<p>This is just a notice to inform you that you have authorized
<?php $api->getApiAppName(); ?> with your user credentials to access
<?php echo $app_name; ?>.  If this is incorrect, <i>please</i> let us know by
responding to this email!  You can also revoke this authorized in your user
preferences at <?php echo $app_name; ?>. If this authorization is approved,
feel free to disregard this email.</p>

<p>Sincerely,<br/> The <?php echo $app_name; ?> Team</p>