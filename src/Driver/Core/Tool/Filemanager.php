<?php

namespace Driver\Core\Tool;

interface Filemanager
{
    public function setup($source_file_path, $dest_file_path = NULL);

    public function get_mimetype();

    public function get_filesize();
}
