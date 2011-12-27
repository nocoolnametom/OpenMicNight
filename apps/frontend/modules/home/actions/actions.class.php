<?php

/**
 * home actions.
 *
 * @package    OpenMicNight
 * @subpackage home
 * @author     Tom Doggett
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class homeActions extends sfActions
{

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request)
    {
        $this->user = $this->getUser()->getGuardUser();
        
        $this->subreddits = array();
        
        $this->episodes = $this->getIndexEpisodes();
        
        $subreddits = sfConfig::get('app_web_app_feed_default_subreddits');
        if (count($subreddits)) {
            $subreddit_data = Api::getInstance()->get('subreddit?domain='
                    . implode(',', $subreddits), true);
            $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
            foreach ($subreddits as $subreddit) {
                    $this->subreddits[$subreddit->getIncremented()] = $subreddit;
            }
        }
    }
    
    protected function getIndexEpisodes()
    {
        $subreddit_ids = array();

        $subreddits = sfConfig::get('app_web_app_feed_default_subreddits');
        if (count($subreddits)) {
            $subreddit_data = Api::getInstance()->get('subreddit?domain='
                    . implode(',', $subreddits), true);
            $subreddits = ApiDoctrine::createQuickObjectArray($subreddit_data['body']);
            foreach ($subreddits as $subreddit) {
                if (!in_array($subreddit->getIncremented(), $subreddit_ids))
                    $subreddit_ids[] = $subreddit->getIncremented();
            }
        }

        $episodes = array();
        if (count($subreddit_ids)) {
            $episode_data = Api::getInstance()->get('episode/future?nwfw=&subreddit_id='
                    . implode(',', $subreddit_ids), true);
            $episodes = ApiDoctrine::createQuickObjectArray($episode_data['body']);
        }

        return $episodes;
    }

}
