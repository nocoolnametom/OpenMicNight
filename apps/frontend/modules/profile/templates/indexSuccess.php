<?php $key = $user->getRedditValidationKey(); ?>
<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_user_atom?reddit_validation_key=' . $key); ?>" type="application/atom+xml" rel="alternate" title="<?php echo $user->getUsername(); ?>" />
<?php end_slot() ?>
<div id="feed_link" style="float: right;"><?php echo link_to(image_tag('rss.svg', array('style' => 'height: 32px; width: auto;')), '@feed_user_atom?reddit_validation_key=' . $key) ?></div>
<h2><?php echo $user->getFullName() ?></h2>
<?php echo link_to1('Edit Profile', 'profile/edit'); ?>

<?php include_partial('api_keys', array('auth_tokens' => $auth_tokens)) ?>