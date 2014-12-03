<?php

namespace Driver\Core\Google;

use Driver\Core\Tool;

class Google implements Tool\Google
{
    protected $client_id;
    protected $client_secret;
    protected $server_key;

    private $access_token;
    private $authorisation_url = 'https://accounts.google.com/o/oauth2/auth';
    private $token_url = 'https://accounts.google.com/o/oauth2/token';
    private $me_url = 'https://www.googleapis.com/plus/v1/people/me';
    private $contacts_url = 'https://www.google.com/m8/feeds/contacts/default/full';

    public function get_authorisation_url($redirect_uri, array $scopes, $should_force_prompt = FALSE)
    {
        return $this->authorisation_url.'?'.http_build_query(array(
            'response_type' => 'code',
            'client_id' => $this->client_id,
            'redirect_uri' => $redirect_uri,
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'approval_prompt' => $should_force_prompt ? 'force' : 'auto'
        ));
    }

    public function get_tokens($redirect_uri, $code)
    {
        return $this->send_request('POST', $this->token_url, array(
            'code' => $code,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => $redirect_uri,
            'grant_type' => 'authorization_code'
        ));
    }

    public function refresh_access_token($refresh_token)
    {
        $response = $this->send_request('POST', $this->token_url, array(
            'refresh_token' => $refresh_token,
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => 'refresh_token'
        ));

        if ( ! isset($response['access_token']))
            return NULL;

        return $response['access_token'];
    }

    public function setup($access_token)
    {
        $this->access_token = $access_token;
    }

    public function get_user()
    {
        return $this->send_request('GET', $this->me_url.'?'.http_build_query(array(
            'key' => $this->server_key
        )));
    }

    public function get_contacts()
    {
        return $this->send_request('GET', $this->contacts_url.'?'.http_build_query(array(
            'alt' => 'json',
            'max-results' => 1000
        )));
    }

    private function send_request($method, $uri, array $data = array())
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $uri);
        if ($method === 'POST')
        {
            curl_setopt($curl, CURLOPT_POST, count($data));
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        if ($this->access_token)
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$this->access_token));
        }

        $response = json_decode(curl_exec($curl), TRUE);
        curl_close($curl);
        return $response;
    }
}
