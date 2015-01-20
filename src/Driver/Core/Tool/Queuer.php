<?php
/**
 * @license MIT
 */

namespace Driver\Core\Tool;

interface Queuer
{
    public function queue($task, $message);
}
