<?php

namespace AssetManager\Service;

use finfo;

class MimeResolver
{
    public function getMimeType($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME);

        return $finfo->file($file);
    }
}
