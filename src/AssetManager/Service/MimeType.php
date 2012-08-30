<?php

namespace AssetManager\Service;

use finfo;

class MimeType
{
    public function detectMimeType($file)
    {
        if (!file_exists($file)) {
            return false;
        }

        $finfo = new finfo(FILEINFO_MIME);
        
        return $finfo->file($file);
    }
}