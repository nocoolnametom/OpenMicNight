<?php

/**
 * AuthorType
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @package    OpenMicNight
 * @subpackage model
 * @author     Tom Doggett
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class AuthorType extends BaseAuthorType
{
    /**
     * Returns the AuthorType description
     *
     * @return string  The object formatted as a string
     */
    public function __toString()
    {
        //return $this->getDescription();
        return ucwords(str_replace('_', ' ', $this->getType()));
    }
    
    public function setIncremented($id)
    {
        $this->_id = array($id);
        $this->set('id', $id, false);
        $this->_lastModified = array();
    }
}
