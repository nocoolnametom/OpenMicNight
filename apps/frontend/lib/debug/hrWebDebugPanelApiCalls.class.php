<?php

class hrWebDebugPanelApiCalls extends sfWebDebugPanel
{

    protected $_api_calls = array();

    public function getTitle()
    {
        $count = $this->getApiCalls();
        $count = is_array($count) ? count($count) : '~';
        if ($count == '~')
            return;
        return '<img src="/sf/sf_web_debug/images/toggle.gif" alt="Docuemntation Shortcuts" height="16" width="16" /> ' . $count . ' api';
    }

    public function getPanelTitle()
    {
        return "API Calls";
    }

    public function getPanelContent()
    {
        $listContent = '<ul id="debug_api_call_list" styel="display: none;">';

        foreach ($this->getApiCalls() as $log) {
            $listContent .= "\n<li><strong>" . $log['location'] . '</strong> ' . $log['getpost'] . ' (' . $log['http_code'] . ')';

            $listContent .= '<ul>';
            if (array_key_exists('url', $log))
                $listContent .= '<li>' . $log['url'] .'</li>';
            if (array_key_exists('request', $log))
                $listContent .= '<li>request: ' . $log['request'] .'</li>';
            if (array_key_exists('response', $log))
                $listContent .= '<li>response: ' . $log['response'] .'</li>';
            $listContent .= "</ul></li>";
        }
        $listContent .= "\n</ul>";

        $toggler = $this->getToggler('debug_api_call_list', 'Toggle list');

        return sprintf('<h3>Api Calls %s</h3>%s', $toggler, $listContent);
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
                }
                $this->_api_calls[] = $log_entry;
            }
        }
        return $this->_api_calls;
    }

}