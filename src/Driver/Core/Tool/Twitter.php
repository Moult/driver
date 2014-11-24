<?php

namespace Driver\Core\Tool;

interface Twitter
{
    public function setup($oauth_token, $oauth_verifier);
    public function get_user();
    public function set_access_tokens($oauth_token, $oauth_token_secret);
    public function get_access_tokens();
    public function get_followers();
    public function direct_message($screen_name, $text);
}
