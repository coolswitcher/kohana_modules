<?php defined('SYSPATH') OR die('No direct script access.');
abstract class Notifier_Core {

    protected $params;
    public static $vendor = 'Pushover';

    public static function factory (array $params = []) {
        $class = 'Notifier_Vendors_' . mb_convert_case(Notifier_Core::$vendor, MB_CASE_TITLE);
        if ( ! class_exists($class))
            throw new Kohana_Exception ('Vendor :vendor not found!', array (':vendor'=>Notifier_Core::$vendor));
        return new $class ($params);
    }

    abstract protected function Send ();

    protected function setParam ($param , $value = '') {
        if ($param)
            $this->params[$param] = $value;
        return $this;
    }

    protected function getParam ($param = '') {
        return Arr::get($this->params, $param);
    }
    
    /*
    Global setters
     */
    public function setTitle ($title = '')
    {
        return $this->setParam ( 'title', $title );
    }    

    public function setMessage ($message = '')
    {
        return $this->setParam ( 'message', $message );
    }   
}                 