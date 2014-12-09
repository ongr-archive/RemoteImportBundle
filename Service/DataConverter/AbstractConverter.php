<?php

namespace ONGR\RemoteImportBundle\Service\DataConverter;

use ONGR\ConnectionsBundle\Pipeline\Event\SourcePipelineEvent;
use ONGR\ConnectionsBundle\Event\SyncTaskCompleteEvent;
use ONGR\ConnectionsBundle\Service\ImportDataDirectory;
use ONGR\RemoteImportBundle\Utils\Exception\NotCountableException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class implements basic behavior data converter.
 */
abstract class AbstractConverter implements \Iterator, FileAwareConverterInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    /**
     * @var ImportDataDirectory
     */
    protected $dir;

    /**
     * @var string
     */
    protected $provider;

    /**
     * @var string
     */
    protected $fileName;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * {@inheritdoc}
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->dir->locateFile($this->fileName);
    }

    /**
     * {@inheritdoc}
     */
    public function getDataType()
    {
        return SyncTaskCompleteEvent::DATA_TYPE_FULL_DOCUMENTS;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function current();

    /**
     * {@inheritdoc}
     */
    abstract public function next();

    /**
     * {@inheritdoc}
     */
    abstract public function key();

    /**
     * {@inheritdoc}
     */
    abstract public function valid();

    /**
     * {@inheritdoc}
     */
    abstract public function rewind();

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        throw new NotCountableException('Converter objects is not countable');
    }

    /**
     * {@inheritdoc}
     */
    public function load(OutputInterface $output = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getRepository()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        throw new \BadMethodCallException();
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Event listener for convert pipeline.
     *
     * @param SourcePipelineEvent $event
     */
    public function onSource(SourcePipelineEvent $event)
    {
        $context = $event->getContext();
        $this->setDir($context['dir']);
        $this->setProvider($context['provider']);
        $this->setFileName($context['file']);
        $this->load($context['output']);
        $event->addSource($this);
    }

    /**
     * @return ImportDataDirectory
     */
    public function getDir()
    {
        return $this->dir;
    }

    /**
     * @param ImportDataDirectory $dir
     *
     * @return static
     */
    public function setDir($dir)
    {
        $this->dir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }
}
