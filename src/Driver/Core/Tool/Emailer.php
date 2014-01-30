<?php
/**
 * @license MIT
 */

namespace Driver\Core\Tool;

interface Emailer
{
    public function set_to($to);

    public function set_cc($cc);

    public function set_bcc($bcc);

    public function set_from($from);

    public function set_html($html);

    public function set_subject($subject);

    public function set_body($body);

    public function send();
}
