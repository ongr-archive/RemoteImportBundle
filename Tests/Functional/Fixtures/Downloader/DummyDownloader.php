<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\RemoteImportBundle\Tests\Functional\Fixtures\Downloader;

use ONGR\RemoteImportBundle\Service\DocumentsFileStorage\AbstractDownloader;

/**
 * Dummy downloader for testing.
 */
class DummyDownloader extends AbstractDownloader
{
    /**
     * {@inheritdoc}
     */
    public function download($dir, $path)
    {
        $filename = 'data.txt';
        file_put_contents("$dir/$path/$filename", 'content_foobar');

        $this->notify($path, 'file');

        return [$filename];
    }
}
