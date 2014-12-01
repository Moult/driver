<?php

namespace Driver\Core\Tool;

interface Twitter
{
    public function get_authorisation_page_url($callback_uri);

    public function get_access_tokens($oauth_token, $oauth_verifier);

    public function setup($oauth_token, $oauth_token_secret);

    public function get_user();

    public function get_followers();

    public function get_users_lookup($user_ids);

    public function send_direct_message($user_id, $text);
}
