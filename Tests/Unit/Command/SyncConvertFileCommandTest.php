<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Command;

use ONGR\RemoteImportBundle\Command\SyncConvertFileCommand;
use ONGR\ConnectionsBundle\EventListener\SyncTaskCompleteBlockerListener;
use ONGR\RemoteImportBundle\Service\DocumentsFileStorage\DataConvertService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test for SyncConvertFileCommand.
 */
class SyncConvertFileCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test ongr:remote:convert-file behavior.
     */
    public function testCommand()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject|DataConvertService $dataConvertService */
        $dataConvertService = $this->getMockBuilder('ONGR\\ConnectionsBundle\\Sync\\Triggers\\TriggersManager')
            ->disableOriginalConstructor()
            ->setMethods(['convert'])
            ->getMock();

        $dataConvertService->expects($this->once())
            ->method('convert')
            ->with('provider', 'file', 'type');

        /** @var \PHPUnit_Framework_MockObject_MockObject|SyncTaskCompleteBlockerListener $blocker */
        $blocker = $this->getMockBuilder('ONGR\\ConnectionsBundle\\EventListener\\SyncTaskCompleteBlockerListener')
            ->disableOriginalConstructor()
            ->setMethods(['setHalt'])
            ->getMock();

        $blocker->expects($this->once())
            ->method('setHalt')
            ->with(true);

        $container = new ContainerBuilder();
        $container->set('ongr_connections.sync_task_complete_blocker_listener', $blocker);
        $container->set('ongr_remote_import.data_convert_service', $dataConvertService);

        $command = new SyncConvertFileCommand();
        $command->setContainer($container);

        /** @var \PHPUnit_Framework_MockObject_MockObject|InputInterface $input */
        $input = $this->getMock('Symfony\\Component\\Console\\Input\\InputInterface');
        $input->expects($this->at(3))->method('getOption')->with('halt')->willReturn(true);
        $input->expects($this->at(4))->method('getArgument')->with('provider')->willReturn('provider');
        $input->expects($this->at(5))->method('getArgument')->with('file')->willReturn('file');
        $input->expects($this->at(6))->method('getOption')->with('type')->willReturn('type');

        /** @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface $output */
        $output = $this->getMock('Symfony\\Component\\Console\\Output\\OutputInterface');

        $command->run($input, $output);
    }
}
