<?php

namespace Driver\Core\Twitter;

use Driver\Core\Tool;

class Twitter implements Tool\Twitter
{
    protected $consumer_key;
    protected $consumer_secret;

    private $request_params;
    private $oauth_token;
    private $oauth_token_secret;

    private $request_token_url = 'https://api.twitter.com/oauth/request_token';
    private $authenticate_url = 'https://api.twitter.com/oauth/authenticate';
    private $access_token_url = 'https://api.twitter.com/oauth/access_token';
    private $verify_credentials_url = 'https://api.twitter.com/1.1/account/verify_credentials.json';
    private $followers_url = 'https://api.twitter.com/1.1/followers/ids.json';
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

    public function get_authorisation_page_url($callback_uri)
    {
        $this->request_params['oauth_callback'] = $callback_uri;
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('POST', $this->request_token_url);

        parse_str($this->send_request('POST', $this->request_token_url), $response);

        unset($this->request_params['oauth_callback']);

        return $this->authenticate_url.'?'.http_build_query(array(
            'oauth_token' => $response['oauth_token']
        ));
    }

    public function get_access_tokens($oauth_token, $oauth_verifier)
    {
        $this->request_params['oauth_token'] = $oauth_token;
        $this->request_params['oauth_verifier'] = $oauth_verifier;
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('POST', $this->access_token_url);

        parse_str($this->send_request('POST', $this->access_token_url), $response);

        unset($this->request_params['oauth_token']);
        unset($this->request_params['oauth_verifier']);
        unset($this->request_params['oauth_signature']);

        return $response;
    }

    public function setup($oauth_token, $oauth_token_secret)
    {
        $this->request_params['oauth_token'] = $oauth_token;
        $this->request_params['oauth_token_secret'] = $oauth_token_secret;
    }

    public function get_user()
    {
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('GET', $this->verify_credentials_url);
        return json_decode($this->send_request('GET', $this->verify_credentials_url), TRUE);
    }

    public function get_followers()
    {
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('GET', $this->followers_url);
        return json_decode($this->send_request('GET', $this->followers_url), TRUE);
    }

    public function direct_message($user_id, $text)
    {
        $this->request_params['oauth_signature'] = $this->build_oauth_signature('POST', $this->direct_messages_url);
        return json_decode($this->send_request('POST', $this->direct_messages_url, array(
            'text' => $text,
            'user_id' => $user_id
        )), TRUE);
    }

    private function generate_nonce()
    {
        return preg_replace('/[^A-Za-z0-9]/', '', base64_encode(bin2hex(openssl_random_pseudo_bytes(32))));
    }

    private function build_oauth_signature($method, $uri)
    {
        ksort($this->request_params);

        $signature_base_string = strtoupper($method).'&'
            .rawurlencode($uri).'&'
            .rawurlencode(http_build_query($this->request_params));

        $signing_key = rawurlencode($this->consumer_secret).'&'
            .rawurlencode(isset($this->request_params['oauth_token_secret']) ? $this->request_params['oauth_token_secret'] : NULL);
        return base64_encode(hash_hmac('sha1', $signature_base_string, $signing_key, TRUE));
    }

    private function send_request($method, $uri, $data = NULL)
    {
        $header = array($this->build_authorisation_header(), 'Expect:');

        $options = array(
           CURLOPT_HTTPHEADER => $header,
           CURLOPT_HEADER => FALSE,
           CURLOPT_URL => $uri,
           CURLOPT_RETURNTRANSFER => TRUE,
           CURLOPT_SSL_VERIFYPEER => FALSE
        );

        if ($method === 'POST')
        {
            $options[CURLOPT_POST] = TRUE;
        }

        if ($data)
        {
            $options[CURLOPT_POSTFIELDS] = $data;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

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
