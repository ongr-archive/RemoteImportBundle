<?php

namespace ONGR\RemoteImportBundle\Service\DocumentsFileStorage;

use ONGR\ConnectionsBundle\Service\ImportDataDirectory;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;

/**
 * Data convert service for raw provider data conversion to json.
 */
class DataConvertService
{
    /**
     * @var ImportDataDirectory
     */
    protected $dir;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var PipelineFactory
     */
    private $pipelineFactory;

    /**
     * @param ImportDataDirectory $dir
     */
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Converts raw provider data to json.
     *
     * @param string               $provider
     * @param string               $file
     * @param string               $type
     * @param OutputInterface|null $output
     *
     * @return array
     */
    public function convert($provider, $file, $type = null, $output = null)
    {
        $target = $provider;
        if ($type) {
            $target .= '-' . $type;
        }

        $pipeline = $this->getPipelineFactory()->create(
            "ongr_convert.$target",
            ['consumers' => [PipelineFactory::CONSUMER_RETURN]]
        );

        $pipeline->setContext(
            [
                'dir' => $this->dir,
                'file' => $file,
                'output' => $output,
                'provider' => $provider,
                'type' => $type,
            ]
        );

        return $pipeline->start()['outputs'];
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return PipelineFactory
     */
    public function getPipelineFactory()
    {
        return $this->pipelineFactory;
    }

    /**
     * @param PipelineFactory $pipelineFactory
     */
    public function setPipelineFactory($pipelineFactory)
    {
        $this->pipelineFactory = $pipelineFactory;
    }
}
