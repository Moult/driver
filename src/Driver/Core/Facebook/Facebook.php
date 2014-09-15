<?php

namespace Driver\Core\Facebook;

use Driver\Core\Tool;

use Facebook\FacebookSession;
use Facebook\FacebookRedirectLoginHelper;
use Facebook\FacebookRequest;
use Facebook\FacebookResponse;
use Facebook\FacebookSDKException;
use Facebook\FacebookRequestException;
use Facebook\FacebookAuthorizationException;
use Facebook\GraphObject;

class Facebook implements Tool\Facebook
{
    protected $app_id;
    protected $app_secret;
    protected $redirect_uri;
    protected $helper;
    protected $scopes = array();
    protected $session;

    public function setup()
    {
        FacebookSession::setDefaultApplication($this->app_id, $this->app_secret);
        $this->helper = new FacebookRedirectLoginHelper($this->redirect_uri);
        $this->session = $this->helper->getSessionFromRedirect();
    }

    public function get_login_url()
    {
        return $this->helper->getLoginUrl($this->scopes);
    }

    public function set_scopes($scopes)
    {
        $this->scopes = $scopes;
    }

    public function check_scopes(array $scopes)
    {
        // @todo re-implement.

        /*foreach ($scopes as $scope)
        {
            if ( ! in_array($scope, $this->instance['data']['scopes']))
            {
                throw new \Exception('Facebook permission scope "'.$scope.'" not allowed');
            }
        }*/
    }

    public function get_user()
    {
        $request = new FacebookRequest( $this->session, 'GET', '/me' );
        $response = $request->execute();
        $graphObject = $response->getGraphObject();

        $user = array(
            'id' => $graphObject->getProperty('id'),
            'email' => $graphObject->getProperty('email'),
            'first_name' => $graphObject->getProperty('first_name'),
            'last_name' => $graphObject->getProperty('last_name'),
            'gender' => $graphObject->getProperty('gender'),
        );

        return $user;
    }

    public function get_user_picture()
    {
        $request = new FacebookRequest($this->session, 'GET', '/me/picture?type=large&redirect=false');
        $response = $request->execute();
        $graphObject = $response->getGraphObject();
        return $graphObject->getProperty('url');
    }

    public function get_friends()
    {
        // @todo implement.
    }
}
