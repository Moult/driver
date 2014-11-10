<?php

namespace Driver\Core\Tool;

interface Facebook
{
    public function setup($code);

    public function get_user_token($fb_exchange_token = NULL);

    public function set_access_token($access_token);

    public function get_access_token();

    public function check_scopes(array $scopes);

    public function get_user();

    public function get_user_picture();

    public function get_friends();
}
