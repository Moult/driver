<?php

namespace Driver\Core\Tool;

interface Encryptor
{
    public function hash_password($password);

    public function verify_password($password, $password_hash);
}
