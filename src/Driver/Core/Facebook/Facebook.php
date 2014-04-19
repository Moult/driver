<?php

namespace Driver\Core\Facebook;

use Driver\Core\Tool;

class Facebook implements Tool\Facebook
{
    protected $facebook;
    protected $token;
    protected $app_id;
    protected $app_secret;
    protected $access;
    protected $instance;

    public function setup($token)
    {
        $this->token = $token;

        $this->facebook = new \Facebook(array(
            'appId'  => $this->app_id,
            'secret' => $this->app_secret,
        ));

        $this->instance = $this->facebook->api('debug_token', 'GET', array('input_token' => $token, 'access_token' => $this->access));

        if (isset($this->instance['data']['error']))
            throw new \Exception('Could not connect to Facebook');
    }

    public function check_scopes(array $scopes)
    {
        foreach ($scopes as $scope)
        {
            if ( ! in_array($scope, $this->instance['data']['scopes']))
            {
                throw new \Exception('Facebook permission scope "'.$scope.'" not allowed');
            }
        }
    }

    public function get_user()
    {
        return $this->facebook->api('/'.$this->instance['data']['user_id']);
    }

    public function get_user_picture()
    {
        return $this->facebook->api('/'.$this->instance['data']['user_id'].'/picture');
    }
}
