<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Tool;

interface Authenticator
{
    public function is_authenticated($id);

    public function authenticate($id);

    public function deauthenticate($id);

    public function get_authenticated_id();
}
