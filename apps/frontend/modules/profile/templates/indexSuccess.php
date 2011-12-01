<h1><?php echo $user->getFullName() ?></h1>
<?php echo link_to1('Edit Profile', 'profile/edit'); ?>

<?php include_partial('api_keys', array('auth_tokens' => $auth_tokens)) ?>