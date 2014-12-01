<?php

namespace ONGR\RemoteImportBundle\Service\DocumentsFileStorage;

use ONGR\ConnectionsBundle\Pipeline\PipelineFactory;
use ONGR\ConnectionsBundle\Service\ImportDataDirectory;

/**
 * Downloads data using injected downloader. Saves contents to file.
 */
class DataDownloadService
{
    /**
     * @var ImportDataDirectory
     */
    protected $dir;

    /**
     * @var string
     */
    protected $currentDir;

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
     * @param string $currentDir
     */
    public function setCurrentDir($currentDir)
    {
        $this->currentDir = $currentDir;
    }

    /**
     * @param string $provider
     * @param bool   $unique
     *
     * @return string
     */
    public function getCurrentDir($provider, $unique)
    {
        if ($this->currentDir !== null) {
            return $this->currentDir;
        }
        $this->currentDir = $this->dir->getCurrentDir($provider, $unique);

        return $this->currentDir;
    }

    /**
     * Run download process.
     *
     * @param string $provider
     * @param string $type
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function download($provider, $type = '')
    {
        $target = $provider;
        if ($type) {
            $target .= '-' . $type;
        }

        $pipeline = $this->getPipelineFactory()->create(
            "ongr_download.$target",
            ['consumers' => [PipelineFactory::CONSUMER_RETURN]]
        );

        $dir = $this->dir->getDataDirPath();
        $path = $this->getCurrentDir($provider, true);
        $pipeline->setContext(['dir' => $dir, 'path' => $path]);
        $files = $pipeline->execute()['outputs'];

        $out = [];

        foreach ($files as $file) {
            $out[] = $path . DIRECTORY_SEPARATOR . $file;
        }

        return $out;
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
