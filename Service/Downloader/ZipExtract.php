<?php

namespace ONGR\RemoteImportBundle\Service\Downloader;

use ZipArchive;

/**
 * Class for extracting files.
 */
class ZipExtract
{
    /**
     * @var ZipArchive
     */
    protected $zip;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->zip = new \ZipArchive();
    }

    /**
     * Extracts specified files contents to its dir.
     *
     * @param string $file
     *
     * @return bool
     */
    public function extract($file)
    {
        $result = false;
        $filePath = dirname($file);

        if (file_exists($file) && $this->zip->open($file) == true) {
            if ($this->zip->extractTo($filePath) == true) {
                $result = true;
            }

            $this->zip->close();
        }

        return $result;
    }
}
