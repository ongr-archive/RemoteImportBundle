<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Command;

use ONGR\RemoteImportBundle\Command\DownloadCommand;
use ONGR\ConnectionsBundle\EventListener\SyncTaskCompleteBlockerListener;
use ONGR\RemoteImportBundle\Service\DocumentsFileStorage\DataDownloadService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Test for DownloadCommand.
 */
class DownloadCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider for testCommand().
     *
     * @return array[]
     */
    public function getTestCommandData()
    {
        return [
            [true, 'provider1', 'images'],
            [false, 'provider2', 'documents'],
        ];
    }

    /**
     * Test ongr:remote:download behavior.
     *
     * @param bool   $halt
     * @param string $provider
     * @param string $type
     *
     * @dataProvider getTestCommandData()
     */
    public function testCommand($halt, $provider, $type)
    {
        $testOutput = ['test1', 'test2'];

        /** @var \PHPUnit_Framework_MockObject_MockObject|SyncTaskCompleteBlockerListener $blocker */
        $blocker = $this->getMockBuilder('ONGR\\ConnectionsBundle\\EventListener\\SyncTaskCompleteBlockerListener')
            ->disableOriginalConstructor()
            ->setMethods(['setHalt'])
            ->getMock();

        $blocker->expects($this->once())
            ->method('setHalt')
            ->with($halt);

        /** @var \PHPUnit_Framework_MockObject_MockObject|DataDownloadService $downloadService */
        $downloadService = $this
            ->getMockBuilder('ONGR\\RemoteImportBundle\\Service\\DocumentsFileStorage\\DataDownloadService')
            ->disableOriginalConstructor()
            ->setMethods(['download'])
            ->getMock();

        $downloadService->expects($this->once())
            ->method('download')
            ->with($provider, $type)
            ->willReturn($testOutput);

        $container = new ContainerBuilder();
        $container->set('ongr_connections.sync_task_complete_blocker_listener', $blocker);
        $container->set('ongr_connections.data_download_service', $downloadService);

        $command = new DownloadCommand();
        $command->setContainer($container);

        /** @var \PHPUnit_Framework_MockObject_MockObject|InputInterface $input */
        $input = $this->getMockBuilder('Symfony\\Component\\Console\\Input\\InputInterface')->getMock();
        $input->expects($this->once())->method('getArgument')->with('provider')->willReturn($provider);
        $input->expects($this->at(3))->method('getOption')->with('halt')->willReturn($halt);
        $input->expects($this->at(5))->method('getOption')->with('type')->willReturn($type);

        /** @var \PHPUnit_Framework_MockObject_MockObject|OutputInterface $output */
        $output = $this->getMock('Symfony\\Component\\Console\\Output\\OutputInterface');
        $output->expects($this->at(0))->method('writeln')->with('<info>Downloaded: test1</info>');
        $output->expects($this->at(1))->method('writeln')->with('<info>Downloaded: test2</info>');

        $command->run($input, $output);
    }
}
