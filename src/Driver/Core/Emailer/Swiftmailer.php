<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Emailer;

class Swiftmailer
{
    protected $instance;
    protected $to;
    protected $from;
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
