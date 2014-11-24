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
    private $tokens;

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

        $this->tokens = $this->get_user_tokens();
    }

    public function get_user()
    {
        $resource_url = 'https://api.twitter.com/1.1/account/verify_credentials.json';

        $this->oauth['oauth_token'] = $this->tokens['oauth_token'];

        unset($this->oauth['oauth_verifier']);
        unset($this->oauth['oauth_signature']);

        $base_string = $this->build_base_string($resource_url, 'GET');
        $this->oauth['oauth_signature'] = $this->build_oauth_signature($base_string, $this->tokens['oauth_token_secret']);

        $response = $this->send_request($resource_url);
        $response = json_decode($response, TRUE);

        return $response;
    }

    public function get_followers()
    {
        $resource_url = 'https://api.twitter.com/1.1/followers/ids.json';

        $this->oauth['oauth_token'] = $this->tokens['oauth_token'];

        unset($this->oauth['oauth_verifier']);
        unset($this->oauth['oauth_signature']);

        $base_string = $this->build_base_string($resource_url, 'GET');
        $this->oauth['oauth_signature'] = $this->build_oauth_signature($base_string, $this->tokens['oauth_token_secret']);

        $response = $this->send_request($resource_url);
        $response = json_decode($response, TRUE);

        return $response;
    }

    public function direct_message($user_id, $text)
    {
        $resource_url = 'https://api.twitter.com/1.1/followers/ids.json';

        $this->oauth['oauth_token'] = $this->tokens['oauth_token'];

        unset($this->oauth['oauth_verifier']);
        unset($this->oauth['oauth_signature']);

        $base_string = $this->build_base_string($resource_url, 'GET');
        $this->oauth['oauth_signature'] = $this->build_oauth_signature($base_string, $this->tokens['oauth_token_secret']);

        $data = array(
            'user_id' => $user_id,
            'text' => $text
        );

        $response = $this->send_request($resource_url, $data);
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

        $response = $this->send_request($request_token_url, TRUE);
        parse_str($response, $response);

        return 'https://api.twitter.com/oauth/authenticate?oauth_token='. $response['oauth_token'];
    }

    public function set_access_tokens($oauth_token, $oauth_token_secret)
    {
        $this->tokens = array(
            'oauth_token' => $oauth_token,
            'oauth_token_secret' => $oauth_token_secret
        );
    }

    public function get_access_tokens()
    {
        return $this->tokens;
    }

    public function get_user_tokens()
    {
        $access_token_url = 'https://api.twitter.com/oauth/access_token';

        $this->oauth['oauth_token'] = $this->oauth_token;
        $this->oauth['oauth_verifier'] = $this->oauth_verifier;
        $base_string = $this->build_base_string($access_token_url);
        $this->oauth['oauth_signature'] = $this->build_oauth_signature($base_string);

        $response = $this->send_request($access_token_url, TRUE);
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

    private function send_request($url, $post_data = NULL)
    {
        $header = array($this->build_authorisation_header(), 'Expect:');

        $options = array(
           CURLOPT_HTTPHEADER => $header,
           CURLOPT_HEADER => FALSE,
           CURLOPT_URL => $url,
           CURLOPT_RETURNTRANSFER => TRUE,
           CURLOPT_SSL_VERIFYPEER => FALSE
        );

        if(! is_null($post_data))
        {
            $options[CURLOPT_POST] = TRUE;

            if(is_array($post_data) && ! empty($post_data))
                $options[CURLOPT_POSTFIELDS] = http_build_query($post_data);
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}