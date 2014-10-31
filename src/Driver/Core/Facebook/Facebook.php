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

    public function get_send_dialog_url($link)
    {
        $params = http_build_query(array(
            'app_id' => $this->app_id,
            'link' => $link,
            'redirect_uri' => $this->redirect_uri
        ));

        return "https://www.facebook.com/dialog/send?".$params;
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
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $result = curl_exec($curl);
        curl_close($curl);

        parse_str($result, $response);
        $this->access_token = $response['access_token'];

        return $this->access_token;
    }

    public function get_long_lived_access_token()
    {
        // @todo implement
        /*GET /oauth/access_token?grant_type=fb_exchange_token&client_id={app-id}&client_secret={app-secret}&fb_exchange_token={short-lived-token}*/
    }

    public function get_user()
    {
        $user = array();

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, 'https://graph.facebook.com/v2.2/me?access_token=' . $this->access_token);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
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
