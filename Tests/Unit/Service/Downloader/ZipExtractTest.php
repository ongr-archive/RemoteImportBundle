<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Service\Downloader;

use ONGR\RemoteImportBundle\Service\Downloader\ZipExtract;

/**
 * Test for ZipExtract.
 */
class ZipExtractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string Testing directory
     */
    protected $dir;

    /**
     * Create test directory before test.
     */
    public function setUp()
    {
        $this->dir = sys_get_temp_dir() . '/zip_test' . uniqid();
        mkdir($this->dir);
    }

    /**
     * Test if zip file is extracted as expected.
     */
    public function testExtract()
    {
        $filename = '/test.zip';
        $testString = 'test file contents';
        $zip = new \ZipArchive();
        $zip->open($this->dir . $filename, \ZipArchive::CREATE);
        $zip->addFromString('testFile', $testString);
        $zip->close();

        $extract = new ZipExtract();
        $result = $extract->extract($this->dir . $filename);

        $this->assertEquals($testString, file_get_contents($this->dir . '/testFile'));
        $this->assertTrue($result);
    }

    /**
     * Delete test directory after test.
     */
    public function tearDown()
    {
        foreach (glob($this->dir . '/*') as $file) {
            unlink($file);
        }
        rmdir($this->dir);
    }
}
