<?php

/**
 * Subreddit filter form.
 *
 * @package    OpenMicNight
 * @subpackage filter
 * @author     Tom Doggett
 * @version    SVN: $Id: sfDoctrineFormFilterTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class SubredditFormFilter extends BaseSubredditFormFilter
{

    public function configure()
    {
        unset($this['bucket_name']);
        unset($this['cf_dist_id']);
        unset($this['cf_domain_name']);
        unset($this['episode_intro']);
        unset($this['episode_outro']);
    }

}
