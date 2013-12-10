<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Emailer;

class Swiftmailer
{
    protected $instance;
    protected $config;
    protected $to;
    protected $from;
    protected $body = '';
    protected $html = NULL;

    public function __construct(Emailer\Config $config)
    {
        $this->instance = \Swift_Message::newInstance();
        $this->config = $config;
        $this->to = array($config->default_to_email => $config->default_to_name);
        $this->from = array($config->default_from_email => $config->default_from_name);
    }

    /**
     * Sets the 'to' email address.
     *
     * Example:
     * $emailer->set_to(array('foo@bar.com' => 'Foo Name', 'bar@foo.com' = 'Bar Name'));
     *
     * @param array $to An array of email addresses to send to.
     *
     * @return void
     */
    public function set_to($to)
    {
        $this->to = $to;
    }

    /**
     * Sets the 'from' email address.
     *
     * Example:
     * $emailer->set_from(array('foo@bar.com' => 'Foo Name', 'bar@foo.com' => 'Bar Name'));
     *
     * @param array $from An array of email addresses to send from.
     *
     * @return void
     */
    public function set_from($from)
    {
        $this->from = $from;
    }

    /**
     * Sets the HTML content of the email.
     *
     * Example:
     * $emailer->set_html('<html>Foo</html>');
     *
     * @param strong $html The HTML alternative part of the message
     *
     * @return void
     */
    public function set_html($html)
    {
        $this->html = $html;
    }

    /**
     * Sets the subject of the email.
     *
     * Example:
     * $emailer->set_subject('Foobar');
     *
     * @param string $subject The subject of the email
     *
     * @return void
     */
    public function set_subject($subject)
    {
        $this->instance->setSubject($subject);
    }

    /**
     * Sets the plaintext body of the email.
     *
     * Example:
     * $emailer->set_body('Foo bar foo bar foo bar');
     *
     * @param string $body The body of the email
     *
     * @return void
     */
    public function set_body($body)
    {
        $this->body = $body;
    }

    /**
     * Sends out a new email.
     *
     * Example:
     * $emailer->send();
     *
     * @return void
     */
    public function send()
    {
        $this->instance->setTo($this->to);
        $this->instance->setFrom($this->from);
        $this->instance->setBody($this->body, 'text/plain');
        if ($this->html !== NULL)
        {
            $this->instance->addPart($this->html, 'text/html');
        }
        if ($this->config->smtp_use_ssl)
        {
            $transport = \Swift_SmtpTransport::newInstance($this->config->smtp_host, $this->config->smtp_port, 'ssl');
        }
        else
        {
            $transport = \Swift_SmtpTransport::newInstance($this->config->smtp_host, $this->config->smtp_port);
        }
        $transport->setUsername($this->config->smtp_user)->setPassword($this->config->smtp_pass);
        $mailer = \Swift_Mailer::newInstance($transport);
        $mailer->send($this->instance);
    }
}
