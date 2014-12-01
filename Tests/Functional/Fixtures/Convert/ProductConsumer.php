<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\RemoteImportBundle\Tests\Functional\Fixtures\Convert;

use ONGR\ConnectionsBundle\Event\SyncTaskCompleteEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\FinishPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\ItemPipelineEvent;
use ONGR\ConnectionsBundle\Pipeline\Event\StartPipelineEvent;
use ONGR\RemoteImportBundle\Tests\Model\ProductModel;
use ONGR\RemoteImportBundle\Utils\Exception\FileCreateException;
use ONGR\RemoteImportBundle\Utils\Json\JsonObjectArrayWriter;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Product consumer for converter pipeline.
 *
 * Writes provided items to file in json format.
 */
class ProductConsumer
{
    const CONVERTED_FILE_SUFFIX = '.converted.json';
    const TMP_FILE_SUFFIX = '.converted.json.tmp';

    /**
     * @var JsonObjectArrayWriter
     */
    private $writer;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Listener for converter to consume data.
     *
     * @param ItemPipelineEvent $event
     *
     * @return string
     */
    public function onConsume(ItemPipelineEvent $event)
    {
        /** @var ProductModel $item */
        $item = $event->getItem();

        $this->writer->push($item);

        return '';
    }

    /**
     * Listener for converter source event to prepare writer.
     *
     * @param StartPipelineEvent $event
     *
     * @throws FileCreateException
     */
    public function onStart(StartPipelineEvent $event)
    {
        $context = $event->getContext();

        $tmpFile = $context['file'] . self::TMP_FILE_SUFFIX;

        /** @var Resource|bool $handle */
        $handle = @fopen($tmpFile, 'w');

        if (!($handle)) {
            throw new FileCreateException('Unable to create a file, check if directory exists');
        }

        $type = empty($context['type']) ? 'default' : $context['type'];
        $this->writer = new JsonObjectArrayWriter(
            $handle,
            $event->getItemCount(),
            [
                'description' => $type,
                'type' => 'full_documents',
            ]
        );
    }

    /**
     * Listener for converter finish event to close writer.
     *
     * @param FinishPipelineEvent $event
     */
    public function onFinish(FinishPipelineEvent $event)
    {
        $this->writer->close();

        $context = $event->getContext();
        rename($context['file'] . self::TMP_FILE_SUFFIX, $context['file'] . self::CONVERTED_FILE_SUFFIX);

        $this->notify(
            $context['file'] . self::TMP_FILE_SUFFIX,
            $context['type'],
            $context['provider']
        );
    }

    /**
     * Creates and dispatches TASK_TYPE_CONVERT event.
     *
     * @param string $file
     * @param string $type
     * @param string $provider
     */
    public function notify($file, $type = '', $provider = '')
    {
        if ($this->dispatcher) {
            $event = new SyncTaskCompleteEvent();
            $event->setOutputFile($file);
            $event->setDataDescription($type);
            $event->setTaskType(SyncTaskCompleteEvent::TASK_TYPE_CONVERT);
            $event->setProvider($provider);
            $this->dispatcher->dispatch(SyncTaskCompleteEvent::EVENT_NAME, $event);
        }
    }

    /**
     * @param EventDispatcherInterface $dispatcher
     *
     * @return static
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }
}
