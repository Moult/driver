<?php

namespace Driver\Core\Facebook;

use Driver\Core\Tool;

class Facebook implements Tool\Facebook
{
    protected $app_id;
    protected $app_secret;
    protected $redirect_uri;
    protected $scopes = array();

    protected $code;
    protected $access_token;

    public function setup($code)
    {
        $this->code = $code;
        $this->access_token = $this->get_access_token();
    }

    public function get_login_url()
    {
        $params = http_build_query(array(
            'client_id' => $this->app_id,
            'redirect_uri' => $this->redirect_uri,
            'scope' => $this->scopes
        ));

        return 'https://www.facebook.com/dialog/oauth?' . $params;
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

    public function get_access_token()
    {
        $params = array(
            'client_id' => $this->app_id,
            'redirect_uri' => $this->redirect_uri,
            'client_secret' => $this->app_secret,
            'code' => $this->code
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://graph.facebook.com/oauth/access_token?' . http_build_query($params));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $result = curl_exec($curl);

        $this->access_token = str_replace("access_token=", '', $result);

        curl_close($curl);

        return $this->access_token;
    }

    public function get_user()
    {
        $user = array();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://graph.facebook.com/v2.1/me?access_token=' . $this->access_token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $result = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($result);

        $user = array(
            'id' => $response->id,
            'email' => $response->email,
            'first_name' => $response->first_name,
            'last_name' => $response->last_name,
            'gender' => $response->gender
        );

        return $user;
    }

    public function get_user_picture()
    {
        // @TODO implement
        return NULL;
    }

    public function get_friends()
    {
        // @todo implement.
        // current api will only return facebook "app" friends
        return array();
    }
}
