<?php

namespace Driver\Core\Tool;

interface Facebook
{
    public function get_login_url($redirect_uri, array $scopes);

    public function get_send_dialog_url($redirect_uri, $link);

    public function get_access_token($redirect_uri, $code);

    public function verify_access_token($access_token);

    public function setup($access_token);

    public function get_user();

    public function get_user_profile_picture();
}
