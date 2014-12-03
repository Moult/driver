<?php

namespace Driver\Core\Facebook;

use Driver\Core\Tool;

class Facebook implements Tool\Facebook
{
    protected $app_id;
    protected $app_secret;

    private $code;
    private $access_token;
    private $oauth_url = 'https://www.facebook.com/dialog/oauth';
    private $send_dialog_url = 'https://www.facebook.com/dialog/send';
    private $access_token_url = 'https://graph.facebook.com/oauth/access_token';
    private $debug_token_url = 'https://graph.facebook.com/debug_token';
    private $me_url = 'https://graph.facebook.com/v2.2/me';
    private $me_picture_url = 'https://graph.facebook.com/v2.2/me/picture';

    public function get_login_url($redirect_uri, array $scopes)
    {
        return $this->oauth_url.'?'.http_build_query(array(
            'client_id' => $this->app_id,
            'redirect_uri' => $redirect_uri,
            'scope' => implode(',', $scopes)
        ));
    }

    public function get_send_dialog_url($redirect_uri, $link)
    {
        return $this->send_dialog_url.'?'.http_build_query(array(
            'app_id' => $this->app_id,
            'link' => $link,
            'redirect_uri' => $redirect_uri
        ));
    }

    public function get_access_token($redirect_uri, $code)
    {
        parse_str(
            $this->send_request($this->access_token_url.'?'.http_build_query(array(
                'client_id' => $this->app_id,
                'redirect_uri' => $redirect_uri,
                'client_secret' => $this->app_secret,
                'code' => $code
            ))),
            $response
        );

        if ( ! isset($response['access_token']))
            return NULL;

        $this->access_token = $response['access_token'];
        return $this->access_token;
    }

    public function verify_access_token($access_token)
    {
        $response = json_decode(
            $this->send_request($this->debug_token_url.'?'.http_build_query(array(
                'input_token' => $access_token,
                'access_token' => $this->get_app_access_token(),
            ))),
            TRUE
        );
        return isset($response['data']['app_id'])
            AND $response['data']['app_id'] == $this->app_id;
    }

    public function setup($access_token)
    {
        $this->access_token = $access_token;
    }

    public function get_user()
    {
        return json_decode($this->send_request($this->me_url.'?'.http_build_query(array(
            'access_token' => $this->access_token
        ))), TRUE);
    }

    public function get_user_profile_picture()
    {
        $response = json_decode($this->send_request($this->me_picture_url.'?'.http_build_query(array(
            'access_token' => $this->access_token,
            'width' => 300,
            'height' => 300,
            'redirect' => 'false'
        ))), TRUE);

        if ($response['data']['is_silhouette'])
            return NULL;

        return $response['data']['url'];
    }

    private function get_app_access_token()
    {
        return $this->send_request($this->access_token_url.'?'.http_build_query(array(
            'client_id' => $this->app_id,
            'client_secret' => $this->app_secret,
            'grant_type' => 'client_credentials'
        )));
    }

    private function send_request($uri)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response_string = curl_exec($curl);
        curl_close($curl);
        return $response_string;
    }
}
