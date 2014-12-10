<?php

namespace ONGR\RemoteImportBundle\Command;

use ONGR\ConnectionsBundle\EventListener\SyncTaskCompleteBlockerListener;
use ONGR\RemoteImportBundle\Service\DocumentsFileStorage\DataConvertService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to convert raw provider file into general format.
 */
class SyncConvertFileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('ongr:remote:convert-file')
            ->setDescription('Converts raw provider file into general format.')
            ->addArgument(
                'provider',
                InputArgument::REQUIRED,
                'Select data provider'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'Select file to sync'
            )
            ->addOption(
                'type',
                't',
                InputOption::VALUE_REQUIRED,
                'Select converter type'
            )->addOption(
                'halt',
                null,
                InputOption::VALUE_NONE,
                'Should all events be stopped after conversion is complete'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $start = microtime(true);

        /** @var DataConvertService $service */
        $service = $this->getContainer()->get('ongr_remote_import.data_convert_service');

        /** @var SyncTaskCompleteBlockerListener $blocker */
        $blocker = $this->getContainer()->get('ongr_connections.sync_task_complete_blocker_listener');
        $blocker->setHalt((bool)$input->getOption('halt'));

        $service->convert(
            $input->getArgument('provider'),
            $input->getArgument('file'),
            $input->getOption('type'),
            $output
        );

        $output->writeln('');
        $output->writeln(sprintf('<info>Job finished in %.2f s</info>', microtime(true) - $start));
        $output->writeln(sprintf('<info>Memory usage: %.2f MB</info>', memory_get_peak_usage() >> 20));
    }
}
