<?php

namespace Driver\Core\Google;

use Driver\Core\Tool;

class Gmail extends Google implements Tool\Gmail
{
    private $feed_url = 'https://www.google.com/m8/feeds/contacts/default/full';

    public function setup($auth_code)
    {
        $this->set_auth_code($auth_code);
        $this->request_access_token();
    }

    public function get_authorise_page_link()
    {
        $link = "https://accounts.google.com/o/oauth2/auth?client_id=".$this->client_id;
        $link.= "&redirect_uri=".$this->redirect_uri;
        $link.= "&scope=https://www.google.com/m8/feeds/&response_type=code";

        return $link;
    }

    public function get_contacts()
    {
        $url = $this->feed_url.'?oauth_token='.$this->get_access_token() . '&alt=json&max-results=1000';

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);

        $response = curl_exec($curl);
        $response = json_decode($response, TRUE);

        $feed_entries = $response['feed']['entry'];

        $contacts = array();

        if($feed_entries)
        {
            foreach($feed_entries as $entry)
            {
                if(isset($entry['gd$email']))
                {
                    $email = $entry['gd$email'][0]['address'];
                    $name = $entry['title']['$t'];

                    if($name == '')
                        list($name) = explode('@', $email);

                    $contacts[] = array(
                        'email' => $email,
                        'name' => $name
                    );
                }
            }
        }

        return $contacts;
    }
}