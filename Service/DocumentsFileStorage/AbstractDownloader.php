<?php

namespace ONGR\RemoteImportBundle\Service\DocumentsFileStorage;

use ONGR\ConnectionsBundle\Event\SyncTaskCompleteEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base downloader.
 */
abstract class AbstractDownloader
{
    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var mixed Data provider passed directly to dispatched event.
     */
    protected $provider;

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Creates and dispatches TASK_TYPE_DOWNLOAD event.
     *
     * @param string $file
     * @param string $type
     */
    public function notify($file, $type = '')
    {
        if ($this->dispatcher) {
            $event = new SyncTaskCompleteEvent();
            $event->setOutputFile($file);
            $event->setDataDescription($type);
            $event->setTaskType(SyncTaskCompleteEvent::TASK_TYPE_DOWNLOAD);
            $event->setProvider($this->provider);
            $this->dispatcher->dispatch(SyncTaskCompleteEvent::EVENT_NAME, $event);
        }
    }

    /**
     * Event listener for download pipeline.
     *
     * @param SourcePipelineEvent $event
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $context = $event->getContext();
        $dir = $context['dir'];
        $path = $context['path'];
        $event->addSource($this->download($dir, $path));
    }

    /**
     * Returns list of files.
     *
     * @param string $dir  Target data directory.
     * @param string $path Target path.
     *
     * @return array Array of file names to dispatch sync task complete event.
     */
    abstract public function download($dir, $path);
}
