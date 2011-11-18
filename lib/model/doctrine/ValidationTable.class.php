<?php

/**
 * ValidationTable
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 */
class ValidationTable extends Doctrine_Table
{
    /**
     * Returns an instance of this class.
     *
     * @return object ValidationTable
     */
    public static function getInstance()
    {
        return Doctrine_Core::getTable('Validation');
    }
    
    public static function storeNewKeys($values)
    {
        //@todo: The following is MySQL specific!  Not good!
        if (!count($values))
            return;
        $sql = 'INSERT IGNORE INTO `validation` (`reddit_key`, `username`) VALUES (';
        $first = true;
        foreach($values as $key => $name)
        {
            $sql .= ($first ? '' : '),(') . "'" . $key . "', '" . $name . "'";
            $first = false;
        }
        $sql .= ');';
        $q = Doctrine_Manager::getInstance()->getCurrentConnection();
        $q->execute($sql);
    }
}