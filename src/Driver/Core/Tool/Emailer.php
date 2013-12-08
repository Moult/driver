<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Tool;

interface Emailer
{
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
    public function set_to($to);

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
    public function set_from($from);

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
    public function set_html($html);

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
    public function set_subject($subject);

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
    public function set_body($body);

    /**
     * Sends out a new email.
     *
     * Example:
     * $emailer->send();
     *
     * @return void
     */
    public function send();
}
