<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RedditObject
 * 
 * Note: you can rely on the main Reddit comment list, but it'd be better to
 * give a Subreddit to this object for extracting keys.  You'll have to run it
 * less.  Running against the main Reddit feed should be redone every minute or
 * so.  Against a subreddit, which has much less traffic, every five minutes
 * will probably suffice.
 *
 * @author doggetto
 */
class RedditObject
{
    protected $location = '';
    protected $iteration_strength;
    protected $data = array();
    protected $children = array();
    protected $comments = array();

    public function RedditObject($reddit_address = 'http://www.reddit.com/', $iteration_strength = 20)
    {
        $this->setLocation($reddit_address);
        $this->setIterationStrength($iteration_strength);
        //$this->appendData();
    }

    public function appendData($after = null, $iteration = 1)
    {
        if ($iteration > $this->getIterationStrength())
            return $this;
        $after_query = $after ? '&after=' . $after : '';
        $json_data = file_get_contents($this->getLocation() . '/comments.json?count=100' . $after_query);
        $append_data = json_decode($json_data, true);
        
        $after = $this->extractComments($append_data);
        
        return $this->appendData($after, $iteration + 1);
    }
    
    public function getLocation()
    {
        return $this->location;
    }
    
    public function setLocation($string)
    {
        $this->location = rtrim($string, '/');
        return $this;
    }
    
    public function setIterationStrength($iteration_strength)
    {
        $this->iteration_strength = $iteration_strength;
    }
    
    public function getIterationStrength()
    {
        return $this->iteration_strength;
    }
    
    public function extractComments($data_array)
    {
        $pattern = '/^[0-9a-f]{32}/i';
        
        $children = $data_array['data']['children'];
        $after = $data_array['data']['after'];
        
        foreach($children as $child)
        {
            $body = $child['data']['body'];
            $author = $child['data']['author'];
            if (!preg_match($pattern, $body, $matches)) {
                continue;
            }
            $key = $matches[0];
            $this->comments[$key] = $author;
        }
        
        return $after;
    }
    
    public function getComments()
    {
        return $this->comments;
    }

}