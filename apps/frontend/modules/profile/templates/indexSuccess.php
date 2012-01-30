<?php /* @var $user sfGuardUser */ ?>
<?php $key = $user->getRedditValidationKey(); ?>
<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_user_atom?reddit_validation_key=' . $key); ?>" type="application/atom+xml" rel="alternate" title="<?php echo $user->getUsername(); ?> Atom" />
<link href="<?php echo url_for('@feed_user_rss?reddit_validation_key=' . $key); ?>" type="application/rss+xml" rel="alternate" title="<?php echo $user->getUsername(); ?> RSS" />
<?php end_slot() ?>
<div id="feed_link"><?php echo link_to(image_tag('rss.svg'), '@feed_user_atom?reddit_validation_key=' . $key) ?></div>
<h2 class="orangeredbar"><?php echo $user->getUsername() ?></h2>

<h3>Profile Information</h3>
<?php echo link_to1('Edit Profile', 'profile/edit'); ?>
<?php echo $user->getTwitterAccount() ?>
<?php echo $user->getWebsite() ?>
<p><?php echo $user->getShortBio() ?></p>

<?php if ($user->getDisplayLocation()): ?>

<?php endif; ?>

<?php include_partial('api_keys', array('auth_tokens' => $auth_tokens)) ?>