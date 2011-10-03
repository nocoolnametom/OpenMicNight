<?php

/**
 * EpisodeTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class EpisodeTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return object EpisodeTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Episode');
    }

    public function getOneEpisodeReleasedWithinSeconds($seconds,
                                                       $episode_id = null)
    {
        $episode_assignments = $this->createQuery()
                ->where('(Episode.release_date - NOW()) < ?',
                           $seconds);
        if ($episode_id)
            $episode_assignments = $episode_assignments->andWhere('Episode.id = ?',
                                                               $episode_id);

        $episode_assignments = $episode_assignments->execute()
                ->getFirst();
        return $episode_assignments;
    }

}