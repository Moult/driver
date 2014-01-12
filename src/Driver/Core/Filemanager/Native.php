<?php
/**
 * @license MIT
 * Full license text in LICENSE file
 */

namespace Driver\Core\Filemanager;

class Native
{
    public function get_mimetype($file_path)
    {
        return explode(';', finfo_file(finfo_open(FILEINFO_MIME), $file_path))[0];
    }
}
