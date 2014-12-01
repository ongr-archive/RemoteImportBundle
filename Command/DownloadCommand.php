<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\RemoteImportBundle\Command;

use ONGR\ConnectionsBundle\EventListener\SyncTaskCompleteBlockerListener;
use ONGR\RemoteImportBundle\Service\DocumentsFileStorage\DataDownloadService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command for initiating data download which will later be converted.
 */
class DownloadCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ongr:sync:download')
            ->setDescription('Downloads raw data from provider repository.')
            ->addArgument(
                'provider',
                InputArgument::REQUIRED,
                'Select data provider'
            )
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'Select downloader type'
            )->addOption(
                'halt',
                null,
                InputOption::VALUE_NONE,
                'Should all the events be stopped after download is complete'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        /** @var DataDownloadService $service */
        $service = $this->getContainer()->get('ongr_connections.data_download_service');

        /** @var SyncTaskCompleteBlockerListener $blocker */
        $blocker = $this->getContainer()->get('ongr_connections.sync_task_complete_blocker_listener');
        $halt = $input->getOption('halt');
        $blocker->setHalt($halt);

        $files = $service->download($input->getArgument('provider'), $input->getOption('type'));

        foreach ($files as $file) {
            $output->writeln(sprintf('<info>Downloaded: %s</info>', $file));
        }

        $output->writeln('');
        $output->writeln(sprintf('<info>Job finished in %.2f s</info>', microtime(true) - $start));
        $output->writeln(sprintf('<info>Memory usage: %.2f MB</info>', memory_get_peak_usage() >> 20));
    }
}
