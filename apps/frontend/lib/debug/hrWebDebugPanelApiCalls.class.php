<?php

class hrWebDebugPanelApiCalls extends sfWebDebugPanel
{

    protected $_api_calls = array();
    protected $_amount = array();

    public function getTitle()
    {
        $count = $this->getApiCalls();
        $count = is_array($count) ? count($count) : '~';
        if ($count == '~')
            return;
        return '<strong>API</strong> ' . $count;
    }

    public function getPanelTitle()
    {
        return ProjectConfiguration::getApplicationName() . " API Calls";
    }

    public function getPanelContent()
    {
        $getContent = '';
        $postContent = '';
        $putContent = '';
        $deleteContent = '';

        foreach ($this->getApiCalls() as $log) {
            $listContent = "\n<li><strong>" . $log['location'] . '</strong> ' . ' (' . $log['http_code'] . ')';

            $listContent .= '<ul>';
            if (array_key_exists('url', $log))
                $listContent .= '<li>' . $log['url'] . '</li>';
            if (array_key_exists('request', $log))
                $listContent .= '<li>request: ' . $log['request'] . '</li>';
            if (array_key_exists('response', $log))
                $listContent .= '<li>response: ' . $log['response'] . '</li>';
            $listContent .= "</ul></li>";
            
            switch($log['getpost'])
            {
            case 'GET':
                $getContent .= $listContent;
                break;
            case 'POST':
                $postContent .= $listContent;
                break;
            case 'PUT':
                $putContent .= $listContent;
                break;
            case 'DELETE':
                $deleteContent .= $listContent;
                break;
            
            }
        }
        
        $getContent = '<ol id="debug_api_get_list" styel="display: none;">' . $getContent . "\n</ol>";
        $postContent = '<ol id="debug_api_post_list" styel="display: none;">' . $postContent . "\n</ol>";
        $putContent = '<ol id="debug_api_put_list" styel="display: none;">' . $putContent. "\n</ol>";
        $deleteContent = '<ol id="debug_api_delete_list" styel="display: none;">' . $deleteContent. "\n</ol>";

        $gettoggler = $this->getToggler('debug_api_get_list', 'Toggle list');
        $posttogger = $this->getToggler('debug_api_get_list', 'Toggle list');
        $puttoggler = $this->getToggler('debug_api_get_list', 'Toggle list');
        $deletetoggler = $this->getToggler('debug_api_get_list', 'Toggle list');
        
        $output = '';
        if ($this->_amount["GET"])
            $output .= sprintf('<h3>GET (%s) %s</h3>%s', $this->_amount["GET"], $gettoggler, $getContent);
        if ($this->_amount["POST"])
            $output .= sprintf('<h3>POST (%s) %s</h3>%s', $this->_amount["POST"], $posttogger, $postContent);
        if ($this->_amount["PUT"])
            $output .= sprintf('<h3>PUT (%s) %s</h3>%s', $this->_amount["PUT"], $puttoggler, $putContent);
        if ($this->_amount["DELETE"])
            $output .= sprintf('<h3>DELETE (%s) %s</h3>%s', $this->_amount["DELETE"], $deletetoggler, $deleteContent);

        return $output;
    }

    public static function listenToLoadDebugWebPanelEvent(sfEvent $event)
    {
        $event->getSubject()->setPanel(
                'api', new self($event->getSubject())
        );
    }

    protected function getApiCalls()
    {
        if (!empty($this->_api_calls))
            return $this->_api_calls;
        $this->_amount = array(
            'GET' => 0,
            'POST' => 0,
            'PUT' => 0,
            'DELETE' => 0,
        );
        $logs = $this->webDebug->getLogger()->getLogs();
        foreach ($logs as $log) {
            if ($log['type'] == 'Api') {
                $log_entry = array();
                $elements = explode('|', $log['message']);
                foreach ($elements as $element) {
                    $items = explode('~', $element);
                    $key = $items[0];
                    $value = $items[1];
                    $log_entry[$key] = $value;
                    if ($key == 'getpost') {
                        $this->_amount[$value] += 1;
                    }
                }
                $this->_api_calls[] = $log_entry;
            }
        }
        return $this->_api_calls;
    }

}