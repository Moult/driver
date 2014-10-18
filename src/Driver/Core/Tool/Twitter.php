<?php

namespace Driver\Core\Tool;

interface Twitter
{
    public function setup($oauth_token, $oauth_verifier);
    public function get_user();
    public function get_user_picture();
}
