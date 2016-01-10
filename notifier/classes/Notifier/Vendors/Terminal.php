<?php
class Notifier_Vendors_Terminal extends Notifier_Core {
    public function __construct ($params)
    {
        $this->params = $params;
        $app_path = Kohana::$config->load('notifier.terminal.path');
        $this->setParam('app_path', $app_path);
    }

    public function setAppPath ($app_path = '')
    {
        $this->app_path = $app_path;
        return $this;
    }   

    public function setSubTitle ($subtitle = '')
    {
        return $this->setParam ('subtitle', $subtitle);
    }      

    public function setSender ($sender = '')
    {
        return $this->setParam ('sender', $sender);
    }     

    public function Send ()
    {
        $cmd = '';
        foreach ($this->params as $key=>&$param)
        {            
            $param = strip_tags ($param);            
            if (!empty ($param) AND $key!=='app_path') {
                $param = preg_replace ('#[^\w\s\-\,\.]#iu', '', $param);
                $cmd .= ' -' . $key . ' ' . escapeshellarg ($param); 
            }
        }           
        return ! (bool) shell_exec ($this->getParam('app_path') . $cmd);
    }
}