<?php

namespace Driver\Core\Tool;

interface Encryptor
{
    public function hash_password($password);
}
