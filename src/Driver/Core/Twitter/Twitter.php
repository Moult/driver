<?php

namespace Driver\Core\Twitter;

use Driver\Core\Tool;

class Twitter implements Tool\Twitter
{
    protected $consumer_key;
    protected $consumer_secret;
    protected $callback_uri;

    protected $oauth;

    private $oauth_token;
    private $oauth_verifier;

    public function __construct()
    {
        $this->oauth = array(
            'oauth_consumer_key' => $this->consumer_key,
            'oauth_nonce' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_timestamp' => time(),
            'oauth_version' => '1.0'
        );
    }

    public function setup($oauth_token, $oauth_verifier)
    {
        $this->oauth_token = $oauth_token;
        $this->oauth_verifier = $oauth_verifier;
    }

    public function get_user()
    {
        $verify_credentials_url = 'https://api.twitter.com/1.1/account/verify_credentials.json';

        $tokens = $this->get_access_tokens();
        $this->oauth['oauth_token'] = $tokens['oauth_token'];

        unset($this->oauth['oauth_verifier']);
        unset($this->oauth['oauth_signature']);

        $base_string = $this->build_base_string($verify_credentials_url, 'GET');
        $this->oauth['oauth_signature'] = $this->build_oauth_signature($base_string, $tokens['oauth_token_secret']);

        $response = $this->send_request($verify_credentials_url, FALSE);
        $response = json_decode($response, TRUE);

        return $response;
    }

    public function get_user_picture()
    {
        return NULL;
    }

    public function get_authorisation_page_url()
    {
        $request_token_url = 'https://api.twitter.com/oauth/request_token';

        $this->oauth['oauth_callback'] = $this->callback_uri;
        $base_string = $this->build_base_string($request_token_url);
        $this->oauth['oauth_signature'] = $this->build_oauth_signature($base_string);

        $response = $this->send_request($request_token_url);
        parse_str($response, $response);

        return 'https://api.twitter.com/oauth/authenticate?oauth_token='. $response['oauth_token'];
    }

    private function get_access_tokens()
    {
        $access_token_url = 'https://api.twitter.com/oauth/access_token';

        $this->oauth['oauth_token'] = $this->oauth_token;
        $this->oauth['oauth_verifier'] = $this->oauth_verifier;
        $base_string = $this->build_base_string($access_token_url);
        $this->oauth['oauth_signature'] = $this->build_oauth_signature($base_string);

        $response = $this->send_request($access_token_url);
        parse_str($response, $response);

        return $response;
    }

    private function build_base_string($uri, $method = 'POST')
    {
        $temp = array();

        ksort($this->oauth);

        foreach($this->oauth as $key => $value)
            $temp[] = "$key=" . rawurlencode($value);

        return $method."&" . rawurlencode($uri) . '&' . rawurlencode(implode('&', $temp));
    }

    private function build_oauth_signature($base_string, $request_token = NULL)
    {
        $composite_key = rawurlencode($this->consumer_secret) . '&' . rawurlencode($request_token);
        return base64_encode(hash_hmac('sha1', $base_string, $composite_key, true));
    }

    private function build_authorisation_header()
    {
        $header = 'Authorization: OAuth ';
        $values = array();

        foreach($this->oauth as $key=>$value)
            $values[] = "$key=\"" . rawurlencode($value) . "\"";

        $header .= implode(', ', $values);

        return $header;
    }

    private function send_request($url, $is_post = TRUE)
    {
        $header = array($this->build_authorisation_header(), 'Expect:');

        $options = array(
           CURLOPT_HTTPHEADER => $header,
           CURLOPT_HEADER => FALSE,
           CURLOPT_URL => $url,
           CURLOPT_RETURNTRANSFER => TRUE,
           CURLOPT_SSL_VERIFYPEER => FALSE
        );

        if($is_post === TRUE)
            $options[CURLOPT_POST] = TRUE;

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}