<?php

namespace Driver\Core\Google;

class Google
{
    const GRANT_TYPE_AUTH_CODE = 'authorization_code';
    const GRANT_TYPE_REFRESH_TOKEN = 'refresh_token';

    private $auth_code;
    private $access_token;
    private $refresh_token;

    protected $client_id;
    protected $client_secret;
    protected $redirect_uri;


    public function request_access_token($refresh_token = NULL)
    {
        $oauth_token_url = 'https://accounts.google.com/o/oauth2/token';

        $params = array(
            'client_id'=> urlencode($this->client_id),
            'client_secret'=> urlencode($this->client_secret),
        );

        if($refresh_token)
        {
            $params['refresh_token'] = $refresh_token;
            $params['grant_type'] = urlencode(self::GRANT_TYPE_REFRESH_TOKEN);
        }
        else
        {
            $params['code'] = $this->auth_code;
            $params['redirect_uri'] = $this->auth_code;
            $params['grant_type'] = urlencode(self::GRANT_TYPE_AUTH_CODE);
        }

        $post = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $oauth_token_url);
        curl_setopt($curl, CURLOPT_POST, count($params));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($curl);
        $response = json_decode($response);

        curl_close($curl);

        if(isset($response->refresh_token))
            $this->refresh_token = $response->refresh_token;

        $this->access_token = $response->access_token;
    }

    public function get_access_token()
    {
        return $this->access_token;
    }

    public function get_refresh_token()
    {
        return $this->refresh_token;
    }

    public function set_auth_code($auth_code)
    {
        $this->auth_code = $auth_code;
    }

    public function revoke_token()
    {
        // @todo curl https://accounts.google.com/o/oauth2/revoke?token={token}
    }
}