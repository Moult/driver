<?php
/**
 * @license MIT
 */

namespace Driver\Core\Emailer;

class Swiftmailer
{
    protected $instance;
    protected $to;
    protected $from;
    protected $cc;
    protected $bcc;
    protected $body = '';
    protected $html = NULL;
    protected $smtp_host;
    protected $smtp_user;
    protected $smtp_pass;
    protected $smtp_port;
    protected $smtp_ssl = TRUE;

    public function __construct()
    {
        $this->instance = \Swift_Message::newInstance();
    }

    public function set_to($to)
    {
        $this->to = $to;
    }

    public function set_cc($cc)
    {
        $this->cc = $cc;
    }

    public function set_bcc($bcc)
    {
        $this->bcc = $bcc;
    }

    public function set_from($from)
    {
        $this->from = $from;
    }

    public function set_html($html)
    {
        $this->html = $html;
    }

    public function set_subject($subject)
    {
        $this->instance->setSubject($subject);
    }

    public function set_body($body)
    {
        $this->body = $body;
    }

    public function send()
    {
        $this->instance->setTo($this->to);
        $this->instance->setCc($this->Cc);
        $this->instance->setBcc($this->Bcc);
        $this->instance->setFrom($this->from);
        $this->instance->setBody($this->body, 'text/plain');
        if ($this->html !== NULL)
        {
            $this->instance->addPart($this->html, 'text/html');
        }
        if ($this->smtp_ssl)
        {
            $transport = \Swift_SmtpTransport::newInstance($this->smtp_host, $this->smtp_port, 'ssl');
        }
        else
        {
            $transport = \Swift_SmtpTransport::newInstance($this->smtp_host, $this->smtp_port);
        }
        $transport->setUsername($this->smtp_user)->setPassword($this->smtp_pass);
        $mailer = \Swift_Mailer::newInstance($transport);
        $mailer->send($this->instance);
    }
}
