<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Service\DocumentsFileStorage;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use ONGR\ConnectionsBundle\Pipeline\PipelineInterface;
use ONGR\RemoteImportBundle\Service\DocumentsFileStorage\DataConvertService;
use ONGR\ConnectionsBundle\Service\ImportDataDirectory;

/**
 * Test for DataConvertService.
 */
class DataConvertServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests convert method.
     */
    public function testConvert()
    {
        /** @var PipelineInterface|\PHPUnit_Framework_MockObject_MockObject $pipeline */
        $pipeline = $this->getMock('\ONGR\ConnectionsBundle\Pipeline\PipelineInterface');
        $pipeline->expects($this->any())->method('execute')->willReturn(['outputs' => ['expected_output.txt']]);

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

        $service = new DataConvertService($dir);
        $service->setPipelineFactory($pipelineFactory);

        $actual = $service->convert('provider', 'type', 'data.txt');
        $this->assertEquals(['expected_output.txt'], $actual);
    }
}
