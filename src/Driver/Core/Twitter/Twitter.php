<?php

namespace Driver\Core\Twitter;

use Driver\Core\Tool;

class Twitter implements Tool\Twitter
{
    protected $consumer_key;
    protected $consumer_secret;

    private $request_params;
    private $oauth_token_secret = NULL;

    private $request_token_url = 'https://api.twitter.com/oauth/request_token';
    private $authenticate_url = 'https://api.twitter.com/oauth/authenticate';
    private $authorise_url = 'https://api.twitter.com/oauth/authorize';
    private $access_token_url = 'https://api.twitter.com/oauth/access_token';
    private $verify_credentials_url = 'https://api.twitter.com/1.1/account/verify_credentials.json';
    private $tweet_url = 'https://api.twitter.com/1.1/statuses/update.json';
    private $followers_url = 'https://api.twitter.com/1.1/followers/ids.json';
    private $users_lookup_url = 'https://api.twitter.com/1.1/users/lookup.json';
    private $direct_messages_url = 'https://api.twitter.com/1.1/direct_messages/new.json';

    public function __construct()
    {
        $this->request_params = array(
            'oauth_consumer_key' => $this->consumer_key,
            'oauth_nonce' => $this->generate_nonce(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );
    }

    public function get_authentication_url($callback_uri)
    {
        return $this->get_auth_url($this->authenticate_url, $callback_uri);
    }

    public function get_authorisation_url($callback_uri)
    {
        return $this->get_auth_url($this->authorise_url, $callback_uri);
    }

    public function get_access_tokens($oauth_token, $oauth_verifier)
    {
        $this->request_params['oauth_token'] = $oauth_token;
        $this->request_params['oauth_verifier'] = $oauth_verifier;
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('POST', $this->access_token_url);

        parse_str($this->send_request('POST', $this->access_token_url), $response);

        unset($this->request_params['oauth_token']);
        unset($this->request_params['oauth_verifier']);

        return $response;
    }

    public function setup($oauth_token, $oauth_token_secret)
    {
        $this->request_params['oauth_token'] = $oauth_token;
        $this->oauth_token_secret = $oauth_token_secret;
    }

    public function get_user()
    {
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('GET', $this->verify_credentials_url);
        return json_decode($this->send_request('GET', $this->verify_credentials_url), TRUE);
    }

    public function tweet($status)
    {
        $this->request_params['status'] = $status;
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('POST', $this->tweet_url);
        return json_decode($this->send_request('POST', $this->tweet_url), TRUE);
    }

    public function get_followers()
    {
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('GET', $this->followers_url);
        return json_decode($this->send_request('GET', $this->followers_url), TRUE);
    }

    public function get_users_lookup($user_ids)
    {
        $this->request_params['user_id'] = implode(',', $user_ids);
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('POST', $this->users_lookup_url);
        unset($this->request_params['user_id']);
        return json_decode($this->send_request('POST', $this->users_lookup_url, array(
            'user_id' => implode(',', $user_ids)
        )), TRUE);
    }

    public function send_direct_message($user_id, $text)
    {
        $this->request_params['text'] = $text;
        $this->request_params['user_id'] = (int) $user_id;
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('POST', $this->direct_messages_url);
        unset($this->request_params['text']);
        unset($this->request_params['user_id']);
        return json_decode($this->send_request('POST', $this->direct_messages_url, array(
            'text' => $text,
            'user_id' => (int) $user_id
        )), TRUE);
    }

    private function generate_nonce()
    {
        return preg_replace('/[^A-Za-z0-9]/', '', base64_encode(bin2hex(openssl_random_pseudo_bytes(16))));
    }

    private function get_auth_url($url, $callback_uri)
    {
        $this->request_params['oauth_callback'] = $callback_uri;
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('POST', $this->request_token_url);

        parse_str($this->send_request('POST', $this->request_token_url), $response);

        unset($this->request_params['oauth_callback']);

        return $url.'?'.http_build_query(array(
            'oauth_token' => $response['oauth_token']
        ));
    }

    private function build_oauth_signature($method, $url)
    {
        ksort($this->request_params);

        $signature_base_string = strtoupper($method).'&'
            .rawurlencode($url).'&'
            .rawurlencode(http_build_query($this->request_params, '', '&', PHP_QUERY_RFC3986));

        $signing_key = rawurlencode($this->consumer_secret).'&'
            .rawurlencode($this->oauth_token_secret);
        return base64_encode(hash_hmac('sha1', $signature_base_string, $signing_key, TRUE));
    }

    private function send_request($method, $url, $data = NULL)
    {
        $header = array($this->build_authorisation_header());

        $options = array(
           CURLOPT_HTTPHEADER => $header,
           CURLOPT_HEADER => FALSE,
           CURLOPT_URL => $url,
           CURLOPT_RETURNTRANSFER => TRUE,
           CURLOPT_SSL_VERIFYPEER => FALSE
        );

        if ($method === 'POST')
        {
            $options[CURLOPT_POST] = 1;
        }

        if ($data)
        {
            $options[CURLOPT_POSTFIELDS] = http_build_query($data);
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);

        curl_close($ch);
        unset($this->request_params['oauth_signature']);

        return $response;
    }

    private function build_authorisation_header()
    {
        $header = 'Authorization: OAuth ';
        $values = array();

        ksort($this->request_params);

        foreach ($this->request_params as $key => $value)
        {
            $values[] = rawurlencode($key).'="'.rawurlencode($value).'"';
        }

        $header .= implode(', ', $values);

        return $header;
    }
}
