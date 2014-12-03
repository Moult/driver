<?php

namespace Driver\Core\Tool;

interface Google
{
    public function get_authorisation_url($redirect_uri, array $scopes, $should_force_prompt);

    public function get_tokens($redirect_uri, $code);

    public function refresh_access_token($refresh_token);

    public function setup($access_token);

    public function get_user();
}
