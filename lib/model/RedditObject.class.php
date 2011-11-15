<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RedditObject
 *
 * @author doggetto
 */
class RedditObject
{
    protected $location = '';
    protected $data = array();
    protected $children = array();
    protected $comments = array();

    public function RedditObject($data = '[{},{"data": ["children": {}]}]')
    {
        $this->setData($data);
    }

    public function setData($data)
    {
        $this->data = json_decode($data, true);
        $this->children = $this->data[1]['data']['children'];
        return $this;
    }

    public function appendData($data)
    {
        $append_data = json_decode($data, true);
        $this->data = array_merge($this->data, $append_data);
        $this->children = array_merge($this->children, $append_data[1]['data']['children']);
        
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getChildren()
    {
        return $this->children;
    }
    
    public function setLocation($string)
    {
        $this->location = $string;
        return $this;
    }
    
    public function getMoreData($child_id)
    {
        if (!$this->location)
            return $this;
        $further_data = file_get_contents($this->location . $child_id . '.json');
        $this->appendData($further_data);
        return $this;
    }

    public function setComments()
    {
        $pattern = '/^[0-9a-f]{32}/i';
        foreach ($this->getChildren() as $key => $child) {
            if ($child['kind'] == 'more') {
                $child_id = $child['data']['id'];
                unset($this->children[$key]);
                $this->getMoreData($child_id);
                return $this;
            }
            $body = $child['data']['body'];
            if (!preg_match($pattern, $body, $matches)) {
                continue;
            }
            $body = $matches[0];
            $name = $child['data']['name'];
            $this->comments[$body] = $name;
        }
        return;
    }
    
    public function getComments()
    {
        return $this->comments;
    }

}