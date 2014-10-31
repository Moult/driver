<?php

namespace Driver\Core\Tool;

interface GoogleConnect
{
    public function setup($auth_code);
    public function get_authorise_page_link();
    public function get_refresh_token();
}