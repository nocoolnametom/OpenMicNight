<?php

/**
 * SubredditTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class SubredditTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return object SubredditTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Subreddit');
    }
    
    public static function grabSubredditIdFromArray($value)
    {
        return (is_array($value) ? $value['subreddit_id'] : null);
    }

    public function getSubredditsNotNeedingEpisodeGeneration()
    {
        $subquery = $this->createQuery()
                ->select('E.subreddit_id')
                ->from('Episode E')
                ->leftJoin('Subreddit S')
                ->groupBy('E.subreddit_id')
                ->where('E.release_date > TIMESTAMPADD(SECOND, S.creation_interval, NOW())')
                ->fetchArray();
        $ids = array_map(array('SubredditTable', 'grabSubredditIdFromArray'), $subquery);
        return $ids;
    }

    public function getSubredditsNeedingEpisodeGeneration($subreddit_name = '%')
    {
        $ids = $this->getSubredditsNotNeedingEpisodeGeneration();
        $subreddits = $this->createQuery()
                        ->where('Subreddit.name LIKE ?', $subreddit_name)
                        ->whereNotIn('Subreddit.id', $ids)
                        ->execute();

        return $subreddits;
    }

}