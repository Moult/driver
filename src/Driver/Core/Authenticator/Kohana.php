<?php
/**
 * @license MIT
 */

namespace
{
    trait Driver_Core_Authenticator_Kohana
    {
        public function is_authenticated($id)
        {
            return (bool) Auth::instance()->get_user() == $id;
        }

        public function authenticate($id)
        {
            Auth::instance()->force_login($id);
        }

        public function deauthenticate($id)
        {
            if (Auth::instance()->get_user() == $id)
                return Auth::instance()->logout();
        }

        public function get_authenticated_id()
        {
            return (int) Auth::instance()->get_user();
        }
    }
}


namespace Driver\Core\Authenticator
{
    class Kohana implements \Driver\Core\Tool\Authenticator
    {
        use \Driver_Core_Authenticator_Kohana;
    }
}
