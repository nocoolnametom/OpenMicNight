<?php

/**
 * EpisodeAssignmentTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class EpisodeAssignmentTable extends Doctrine_Table
{

    /**
     * Returns an instance of this class.
     *
     * @return object EpisodeAssignmentTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('EpisodeAssignment');
    }
    
    /**
     * Pulls the 'id' from a given sub-array.
     *
     * @param array $value  The sub-array from which to take the id.
     * @return int          The id contained in the sub-array.
     */
    public static function grabIdFromArray($value)
    {
        return (is_array($value) ? $value['id'] : null);
    }
    
    public static function findAllInArray($id_array = array())
    {
        $episode_assignments = $this->createQuery()
                ->whereIn('EpisodeAssignment.id', $id_array)
                ->execute();
        return $episode_assignments;
    }

    /**
     * Deletes all future EpisodeAssignments in a given Subreddit for a
     * particular User.
     *
     * @param int $subreddit_id The incremented ID of a Subreddit object
     * @param int $user_id      The incremented ID of an sfGuardUser object
     */
    public function deleteBySubredditIdAndUserId($subreddit_id, $user_id)
    {

        $subreddit_id = (int) ($subreddit_id);
        $subquery = $this->createQuery()
                ->select('EpisodeAssignment.id')
                //->from('EpisodeAssignment')
                ->leftJoin('EpisodeAssignment.Episode Episode')
                ->where('Episode.release_date > NOW()')
                ->andWhere('Episode.subreddit_id = ?', $subreddit_id)
                ->andWhere('EpisodeAssignment.sf_guard_user_id = ?', $user_id)
                ->groupBy('EpisodeAssignment.id')
                ->fetchArray();
        $ids = array_map(array('EpisodeAssignmentTable', 'grabIdFromArray'), $subquery);
        $query = $this->createQuery()
                ->delete()
                ->from('EpisodeAssignment')
                ->whereIn('EpisodeAssignment.id', $ids);
        $query->execute();
    }

    /**
     * Returns the first future EpisodeAssignment identified by a given User,
     * AuthorType, and Subreddit.  Since there should only be one entry in all
     * future EpisodeAssignments for these threee identifiying factors, this
     * function should return the only EpisodeAssignment possible.
     * 
     * Returns null on not finding an Episode Assignment.
     * 
     * Similar to getFirstByEpisodeAuthorTypeAndSubreddit(), but is concerned
     * about finding the EpisodeAssignment for a particular User.
     *
     * @param int $author_type_id The incremented ID of an AuthorType object
     * @param int $user_id        The incremented ID of an sfGuardUser object
     * @param int $subreddit_id   The incremented ID of a Subreddit object
     * @return EpisodeAssignment  The EpisodeAssignment identified by the given
     *                            parameters.
     */
    public function getFirstByUserAuthorTypeAndSubreddit($author_type_id,
                                                         $user_id, $subreddit_id)
    {
        $episode_assignments = $this->createQuery()
                ->leftJoin('EpisodeAssignment.Episode Episode')
                ->where('EpisodeAssignment.author_type_id = ?', $author_type_id)
                ->andWhere('EpisodeAssignment.sf_guard_user_id = ?', $user_id)
                ->andWhere('Episode.subreddit_id = ?', $subreddit_id)
                ->andWhere('Episode.release_date > NOW()')
                ->orderBy('EpisodeAssignment.created_at ASC')
                ->execute()
                ->getFirst();
        return $episode_assignments;
    }
    
    public function getByIdHash($id_hash, $subreddit_id)
    {
        $episode_assignments = $this->createQuery()
                ->leftJoin('EpisodeAssignment.Episode Episode')
                ->where('EpisodeAssignment.id_hash = ?', $id_hash)
                ->andWhere('Episode.subreddit_id = ?', $subreddit_id)
                ->andWhere('EpisodeAssignment.missed_deadline != ?', 1)
                ->andWhere('Episode.is_approved != ?', 1)
                ->execute()
                ->getFirst();
        return $episode_assignments;
    }

    /**
     * Returns the first future EpisodeAssignment identified by a given Episode,
     * AuthorType, and Subreddit.  Since there should only be one entry in all
     * future EpisodeAssignments for these threee identifiying factors, this
     * function should return the only EpisodeAssignment possible.
     * 
     * Returns null on not finding an Episode Assignment.
     * 
     * Similar to getFirstByUserAuthorTypeAndSubreddit(), but is concerned
     * about finding the EpisodeAssignment for a particular Episode.
     *
     * @param int $author_type_id The incremented ID of an AuthorType object
     * @param int $episode_id     The incremented ID of an Episode object
     * @param int $subreddit_id   The incremented ID of a Subreddit object
     * @return EpisodeAssignment  The EpisodeAssignment identified by the given
     *                            parameters.
     */
    public function getFirstByEpisodeAuthorTypeAndSubreddit($author_type_id,
                                                            $episode_id,
                                                            $subreddit_id)
    {
        $episode_assignments = $this->createQuery()
                ->leftJoin('EpisodeAssignment.Episode Episode')
                ->where('EpisodeAssignment.author_type_id = ?', $author_type_id)
                ->andWhere('EpisodeAssignment.episode_id = ?', $episode_id)
                ->andWhere('Episode.subreddit_id = ?', $subreddit_id)
                ->andWhere('Episode.release_date > NOW()')
                ->orderBy('EpisodeAssignment.created_at ASC')
                ->execute()
                ->getFirst();
        return $episode_assignments;
    }
    
    /**
     * Returns the first future EpisodeAssignment identified by a given Episode,
     * User, and Subreddit.  Since there should only be one entry in all
     * future EpisodeAssignments for these threee identifiying factors, this
     * function should return the only EpisodeAssignment possible.
     * 
     * Returns null on not finding an Episode Assignment.
     * 
     * Similar to getFirstByUserAuthorTypeAndSubreddit(), but is concerned
     * about finding the EpisodeAssignment for a particular User.
     *
     * @param int $aut$user_id    The incremented ID of an sfGuardUser object
     * @param int $episode_id     The incremented ID of an Episode object
     * @param int $subreddit_id   The incremented ID of a Subreddit object
     * @return EpisodeAssignment  The EpisodeAssignment identified by the given
     *                            parameters.
     */
    public function getFirstByUserEpisodeAndSubreddit($user_id,
                                                            $episode_id,
                                                            $subreddit_id)
    {
        $episode_assignments = $this->createQuery()
                ->leftJoin('EpisodeAssignment.Episode Episode')
                ->where('EpisodeAssignment.sf_guard_user_id = ?', $user_id)
                ->andWhere('EpisodeAssignment.episode_id = ?', $episode_id)
                ->andWhere('Episode.subreddit_id = ?', $subreddit_id)
                ->andWhere('Episode.release_date > NOW()')
                ->orderBy('EpisodeAssignment.created_at ASC')
                ->execute()
                ->getFirst();
        return $episode_assignments;
    }

    public function getAllByEpisodeId($episode_id)
    {
        $episode_assignments = $this->createQuery()
                ->where('EpisodeAssignment.episode_id = ?', $episode_id)
                ->execute();
        return $episode_assignments;
    }
}