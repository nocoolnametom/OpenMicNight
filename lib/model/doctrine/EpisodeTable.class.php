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

    /**
     * Returns the first Episode found where the release date is within a given
     * number of seconds from now.
     * 
     * Optionally, the incremented ID of a sepcific Episode can also be given;
     * if it is within the given seconds, the Episode object referred to will be
     * returned.
     * 
     * Returns null on not finding any Episodes within the given time frame.
     *
     * @param int $seconds    The number of seconds from now to check against.
     * @param int $episode_id The incremented ID of an Episode object
     * @return Episode        The first found Episode object.
     */
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