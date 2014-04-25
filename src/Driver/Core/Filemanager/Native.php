<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Filemanager;

class Native
{
    private $source_path;
    private $dest_path;

    public function setup($source_path, $dest_path = NULL)
    {
        $this->source_path = $source_path;
        $this->dest_path = $dest_path;
    }

    public function get_mimetype()
    {
        return explode(';', finfo_file(finfo_open(FILEINFO_MIME), $this->source_path))[0];
    }

    public function get_filesize()
    {
        return filesize($this->source_path);
    }
}
