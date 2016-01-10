<?php
class Notifier_Vendors_Pushover extends Notifier_Core {	    
    public function __construct ($params)
    {
        $this->params = $params;
    }

    public function setUser ($user = '')
    {
        return $this->setParam ('user', $user);
    }

    public function setApiKey ($token = '')
    {
        return $this->setParam ('token', $token);
    }  	

	public function Send ()
	{        
        $config = (array) Kohana::$config->load('notifier.pushover');
        $this->params = Arr::merge($this->params, $config);
        $post_params = ['user' => Notifier_Core::getParam('user'),
            'token'        => Notifier_Core::getParam('token'),                
            'title'        => Notifier_Core::getParam('title'),
            'message'      => Notifier_Core::getParam('message'),
            'html'         => (int) Notifier_Core::getParam('html')]; 
        $request = Request::factory('https://api.pushover.net/1/messages.json')
          ->method(Request::POST)
            ->post($post_params);
        $response = $request->execute();
        return $response->body();
	}
}