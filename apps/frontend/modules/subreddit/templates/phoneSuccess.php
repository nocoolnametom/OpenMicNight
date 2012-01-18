<?php slot('atom_feed') ?>
<link href="<?php echo url_for('@feed_subreddit_atom?domain=' . $subreddit->getDomain()) ?>" type="application/atom+xml" rel="alternate" title="<?php echo $subreddit ?> Atom" />
<link href="<?php echo url_for('@feed_subreddit_rss?domain=' . $subreddit->getDomain()) ?>" type="application/rss+xml" rel="alternate" title="<?php echo $subreddit ?> RSS" />
<?php end_slot() ?>
<h2><?php echo $subreddit ?></h2>
<h3>Tropo Telephone Integration</h3>
<?php echo link_to('Back', 'subreddit/show?domain=' . $subreddit->getDomain()) ?>
<div id="tropo_instructions">
    <div style="padding-top: 1em;">You can add the ability to record episodes using a phone for those on
        your subreddit who may not have access to regular recording equipment.
        While the quality is much poorer than recording on a computer with a
        microphone, it can be a great asset to help people participate
        more.</div>

    <div style="padding-top: 1em;">To enable phone recording, you will have to
        create an account at
        <?php echo link_to('Tropo.com', 'http://tropo.com') ?>.  Tropo has
        specialized "applications" that handle telephone interactions, so we'll
        need to build an application specifically for your subreddit.
        Development accounts are free, but technically the development between
        <?php echo ProjectConfiguration::getApplicationName() ?> and Tropo has
        already been done, so to be fair you should sign up for a production
        account.  Tropo should have the pricing listed on their home page.  We
        can't prevent you from using a development account, but it's not a nice
        thing to do.</div>

    <div style="padding-top: 1em;">Once you have an account, go to your account
        page, and under the "Your Application" area create  a new application.
        For the style select "Tropo Scripting"; this means that
        <?php echo ProjectConfiguration::getApplicationName() ?> will give
        Tropo the information it needs to answer calls.</div>

    <div style="padding-top: 1em;">You can give your Tropo application any name
        you want, but it'd be a good idea to have something that will remind you
        that it's handling the <?php echo $subreddit ?> subreddit in
        <?php echo ProjectConfiguration::getApplicationName() ?>.  In the box
        that asks what URL powers your app, enter the following:
        <blockquote><?php echo url_for('@subreddit_tropo?domain=' . $subreddit->getDomain(), true) ?></blockquote>
        Then click the button to create your application.</div>

    <div style="padding-top: 1em;">The phone numbers section should display a
        list of free computer-based telephony access numbers.  To add a phone
        number that a physical phone can dial you'll have to add a new phone
        number.  Different numbers have different prices, so makes sure you get
        the cheapest number that will serve the majority of your users.</div>

    <div style="padding-top: 1em;">Once you have the phone numbers you want,
        enter them into the form on this page.  These phone numbers will then
        appear to users as an option for recording their episodes.</div>

    <div style="padding-top: 1em;">It's your responsibility to keep the phone
        numbers contained here on <?php echo ProjectConfiguration::getApplicationName() ?>
        accurate to what your Tropo application offers.  To turn off Tropo
        integration simply delete all phone numbers both her and on
        Tropo.</div>
</div>

<div id="phone_add_form">
    <?php use_stylesheets_for_form($form) ?>
    <?php use_javascripts_for_form($form) ?>
    
    <?php if (count($phone_numbers)): ?>
    <ul>
    <?php foreach($phone_numbers as $number): ?>
        <li><?php echo link_to('Remove', 'subreddit/removephone?id=' . $number->getIncremented()) ?> <?php echo $number->getNumber() ?></il>
    <?php endforeach; ?>
    </ul>
    <?php endif;?>

    <form action="<?php echo url_for('subreddit/phone?domain=' . $subreddit->getDomain()) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
        <table>
            <tfoot>
                <tr>
                    <td colspan="2">
                        <?php echo $form->renderHiddenFields(false) ?>
                        <input type="submit" value="Save" />
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php echo $form->renderGlobalErrors() ?>
                <?php echo $form->renderUsing('table'); ?>
            </tbody>
        </table>
    </form>
</div>