<?php

namespace Driver\Core\Tool;

interface Facebook
{
    public function setup($code);

    public function check_scopes(array $scopes);

    public function get_user();

    public function get_user_picture();

    public function get_friends();

    public function get_long_lived_access_token();
}
