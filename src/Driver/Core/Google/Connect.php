<?php

namespace Driver\Core\Google;

use Driver\Core\Tool;

class Connect extends Google implements Tool\GoogleConnect
{
    public function setup($auth_code)
    {
        $this->set_auth_code($auth_code);
    }

    public function get_authorise_page_link()
    {
        $link = "https://accounts.google.com/o/oauth2/auth?client_id=".$this->client_id;
        $link.= "&redirect_uri=".$this->redirect_uri;
        $link.= "&scope=https://www.googleapis.com/auth/plus.login&response_type=code";

        return $link;
    }

    public function get_refresh_token()
    {
        $this->request_access_token();
        return $this->get_refresh_token();
    }
}