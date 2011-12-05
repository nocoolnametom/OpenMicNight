<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ApiDoctrineQuick
 *
 * @author doggetto
 */
class ApiDoctrineQuick
{
    private $_data;

    public function ApiDoctrineQuick($data)
    {
        $this->fromArray($data);
        return $this;
    }

    public function fromArray($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function __call($method, $arguments)
    {
        $lcMethod = strtolower($method);

        if (substr($lcMethod, 0, 3) == 'get') {
            $by = substr($method, 3, strlen($method));
            $method = 'get';
        }

        if (isset($by)) {
            $key = trim($this->from_camel_case($by), '_');

            if ($this->array_key_exists_nc($key, $this->_data)) {
                $array_key = $this->array_key_exists_nc($key, $this->_data);
                if (is_array($this->_data[$array_key]))
                    return new ApiDoctrineQuick($this->_data[$array_key]);
                else
                    return $this->_data[$array_key];
            }
            throw new sfException('Unknown key requested:' . $key);
        }

        throw new sfException('Unknown method requested:' . $method);
    }

    public function getIncremented()
    {
        if (is_string($this->_data))
            die(var_dump($this->_data));
        return (is_array($this->_data) && array_key_exists('id', $this->_data)) ? $this->_data['id']
                    : null;
    }

    protected function from_camel_case($str)
    {
        $str[0] = strtolower($str[0]);
        $func = create_function('$c', 'return "_" . strtolower($c[1]);');
        return preg_replace_callback('/([A-Z])/', $func, $str);
    }

    protected function array_key_exists_nc($key, $search)
    {
        if (array_key_exists($key, $search)) {
            return $key;
        }
        if (!(is_string($key) && is_array($search) && count($search))) {
            return false;
        }
        $key = strtolower($key);
        foreach($search as $k => $v) {
            if (strtolower($k) == $key) {
                return $k;
            }
        }
    }
}