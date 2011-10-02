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
    
    public function getSubredditsNeedingEpisodeGeneration($subreddit_name = '%')
    {
        $subquery = $this->createQuery()
                ->select('Subreddit.id')
                ->leftJoin('Episode')
                ->groupBy('Episode.release_date')
                ->having('Episode.release_date > NOW()');
        $subreddits = @$this->createQuery()
                ->where('Subreddit.name LIKE :name',
                        array(':name' => $subreddit_name))
                ->whereNotIn('Subreddit.id', $subquery)
                ->execute();
        
        return $subreddits;
    }
}