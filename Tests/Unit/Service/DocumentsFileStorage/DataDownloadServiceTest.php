<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Service\DocumentsFileStorage;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use ONGR\ConnectionsBundle\Pipeline\PipelineInterface;
use ONGR\RemoteImportBundle\Service\DocumentsFileStorage\DataDownloadService;
use ONGR\ConnectionsBundle\Service\ImportDataDirectory;

/**
 * Test for DataDownloadService.
 */
class DataDownloadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests download method.
     */
    public function testDownload()
    {
        /** @var PipelineInterface|\PHPUnit_Framework_MockObject_MockObject $pipeline */
        $pipeline = $this->getMock('\ONGR\ConnectionsBundle\Pipeline\PipelineInterface');
        $pipeline->expects($this->any())->method('execute')->willReturn(['outputs' => ['data.txt']]);

        /** @var PipelineFactory|\PHPUnit_Framework_MockObject_MockObject $pipelineFactory */
        $pipelineFactory = $this->getMock('\ONGR\ConnectionsBundle\Pipeline\PipelineFactory');
        $pipelineFactory->expects($this->any())->method('create')->willReturn($pipeline);

        /** @var ImportDataDirectory|\PHPUnit_Framework_MockObject_MockObject $dir */
        $dir = $this->getMock(
            'ONGR\ConnectionsBundle\Service\ImportDataDirectory',
            ['getDataDirPath', 'getCurrentDir'],
            [],
            '',
            false
        );
        $dir->expects($this->any())->method('getDataDirPath')->will($this->returnValue('/base/path'));
        $dir->expects($this->any())->method('getCurrentDir')->will($this->returnValue('unique/path'));

        $service = new DataDownloadService($dir);
        $service->setPipelineFactory($pipelineFactory);

        $actual = $service->download('provider', 'type');
        $this->assertEquals(['unique/path/data.txt'], $actual);
    }

    /**
     * Test getCurrentDir forced dir branch.
     */
    public function testGetCurrentDir()
    {
        $service = new DataDownloadService(null);
        $service->setCurrentDir('current/dir');
        $this->assertEquals('current/dir', $service->getCurrentDir('provider_foo', false));
    }
}
