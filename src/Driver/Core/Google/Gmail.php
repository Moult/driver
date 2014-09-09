<?php

namespace Driver\Core\Google;

use Driver\Core\Tool;

class Gmail implements Tool\Gmail
{
    protected $client_id;
    protected $client_secret;
    protected $redirect_uri;

    private $access_token;
    private $feed_url = 'https://www.google.com/m8/feeds/contacts/default/full';

    public function setup($auth_code)
    {
        $this->access_token = $this->get_access_token($auth_code);
    }

    public function get_authorise_link()
    {
        $link = "https://accounts.google.com/o/oauth2/auth?client_id=".$this->client_id;
        $link.= "&redirect_uri=".$this->redirect_uri;
        $link.= "&scope=https://www.google.com/m8/feeds/&response_type=code";

        return $link;
    }

    public function get_contacts()
    {
        $url = $this->feed_url.'?oauth_token='.$this->access_token;

        $curl = curl_init();

        curl_setopt($curl,CURLOPT_URL, $url);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl,CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($curl, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($curl);

        curl_close($curl);

        $emails = array();

        $xml = new \SimpleXMLElement($response);
        $xml->registerXPathNamespace('gd', 'http://schemas.google.com/g/2005');

        $result = $xml->xpath('//gd:email');

        foreach ($result as $title)
        {
            $emails[] = (string) $title->attributes()->address;
        }

        return $emails;
    }

    private function get_access_token($code)
    {
        $fields=array(
            'code'=> $code,
            'client_id'=> urlencode($this->client_id),
            'client_secret'=> urlencode($this->client_secret),
            'redirect_uri'=> $this->redirect_uri,
            'grant_type'=>  urlencode('authorization_code')
        );

        $post = http_build_query($fields, '', '&', PHP_QUERY_RFC3986);

        $curl = curl_init();

        curl_setopt($curl,CURLOPT_URL,'https://accounts.google.com/o/oauth2/token');
        curl_setopt($curl,CURLOPT_POST,5);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$post);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);

        $result = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($result);

        return $response->access_token;
    }
}