<?php

namespace Fox\RemoteImportBundle\Tests\Unit\Service\DocumentsFileStorage;

use ONGR\ConnectionsBundle\Event\SyncTaskCompleteEvent;
use ONGR\RemoteImportBundle\Service\DocumentsFileStorage\AbstractDownloader;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test for AbstractDownloader.
 */
class AbstractDownloaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tests notify method.
     */
    public function testNotifier()
    {
        $event = new SyncTaskCompleteEvent();
        $event->setTaskType(SyncTaskCompleteEvent::TASK_TYPE_DOWNLOAD);
        $event->setOutputFile('unique/path/file.xml');
        $event->setProvider('someProvider');
        $event->setDataDescription('');

        /** @var EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $dispatcher->expects($this->any())->method('dispatch')->with(SyncTaskCompleteEvent::EVENT_NAME, $event);

        /** @var AbstractDownloader $downloader */
        $downloader = $this->getMockForAbstractClass(
            'ONGR\RemoteImportBundle\Service\DocumentsFileStorage\AbstractDownloader'
        );

        $downloader->setDispatcher($dispatcher);
        $downloader->setProvider('someProvider');
        $downloader->notify('unique/path/file.xml');

        $this->assertEquals($dispatcher, $downloader->getDispatcher());
    }
}
