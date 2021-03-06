<?php

/**
 * sfGuardUserTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class sfGuardUserTable extends PluginsfGuardUserTable
{
    /**
     * Returns an instance of this class.
     *
     * @return object sfGuardUserTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('sfGuardUser');
    }
    
    public static function getIfValidatedUserHasUsername($username)
    {
        $query = Doctrine_Query::create()
                ->select('COUNT(id) AS count')
                ->from('sfGuardUser')
                ->where('is_validated = 1')
                ->andWhere('username = ?', $username)
                ->groupBy('id')
                ->fetchArray();
        return (count($query) ? true : false);
    }
    
    public function getUsersToBeValidated()
    {
        $sql = "SELECT `sf_guard_user`.`id`
FROM `sf_guard_user`
JOIN `validation` ON ( `validation`.`reddit_key` = `sf_guard_user`.`reddit_validation_key`
AND `validation`.`username` = `sf_guard_user`.`username`)
WHERE `sf_guard_user`.`is_validated` = '0' OR `sf_guard_user`.`is_validated` IS NULL";
        
        $q = Doctrine_Manager::getInstance()->getCurrentConnection();
        $results = $q->fetchColumn($sql);
        
        return $results;
    }
    
    public function validateUsers($ids)
    {   
        $rows = $this->createQuery()
                ->update()
                ->set('sfGuardUser.is_validated', true)
                ->whereIn('sfGuardUser.id', $ids)
                ->execute();
        return $rows;
    }
    
    public function getOneDayEmailReminders()
    {
        $one_day = 86400;
        $two_days = 86400 * 2;
        $yesterday = date('Y-m-d H:i:s', time() - $one_day);
        $day_before_yesterday = date('Y-m-d H:i:s', time() - $two_days);
        
        $query = $this->createQuery()
                ->where('authorized_at <= ?', $yesterday)
                ->andWhere('authorized_at >= ?', $day_before_yesterday)
                ->andWhere('is_validated = ? OR is_validated IS NULL', 0)
                ->execute();
        return $query;
    }
    
    public function getOneWeekEmailReminders()
    {
        $one_day = 86400;
        $one_week = 604800;
        $one_day_past_a_week = $one_week + $one_day;
        $a_week_ago = date('Y-m-d H:i:s', time() - $one_week);
        $a_week_and_a_day = date('Y-m-d H:i:s', time() - ($one_week + $one_day));
        
        $query = $this->createQuery()
                ->where('authorized_at <= ?', $a_week_ago)
                ->andWhere('authorized_at >= ?', $a_week_and_a_day)
                ->andwhere('(is_validated = ? OR is_validated IS NULL)', array(0))
                ->execute();
        return $query;
    }
}