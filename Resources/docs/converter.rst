Convert Command
===============
Convert command converts provided files to raw elastic search format.

Working with convert Command
----------------------------

Converters listen to pipeline events to process items. 

For example command ``ongr:remote:convert-file provider filename.txt``
will use ``ongr.pipeline.ongr_convert.provider.*`` events.

Example implementation:
~~~~~~~~~~~~~~~~~~~~~~~

Converter:

.. code-block:: php

    class ProductsConverter extends AbstractXMLConverter
    {
        /**
         * @var string The tag which will be looked for in xml to distinguish object
         */
        protected $objectTag = 'product';
    
        /**
         * {@inheritdoc}
         */
        public function getObjectTag()
        {
            return $this->objectTag;
        }
    
        /**
         * {@inheritdoc}
         */
        protected function convertItem(\SimpleXMLElement $item)
        {
            $model = new ProductModel;
    
            $model->id = (string)$item->attributes()[0];
            $model->title = (string)$item->title;
            $model->sku = (integer)$item->sku;
            $model->description = (string)$item->description;
            $model->price = (float)$item->price;
            $model->image = [$item->image];
            $model->manufacturer = (string)$item->manufacturer;
            $model->longDescription = (string)$item->longDescription;
            $model->url = [$item->url];
            $model->url_lowercased = [$item->url_lowercased];
    
            return $model;
        }
    }

..

Consumer:

.. code-block:: php

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
    
..

And yml file:

.. code-block:: yml

    project.converter.provider.source:
        class: %project.converter.provider.source.class%
        calls:
            - [ setDispatcher, [ @event_dispatcher ] ]
        tags:
            - name: kernel.event_listener
              event: ongr.pipeline.ongr_convert.provider.source
              method: onSource

    project.converter.provider.consumer:
        class: %project.converter.provider.consumer.class%
        calls:
            - [ setDispatcher, [ @event_dispatcher ] ]
        tags:
            - name: kernel.event_listener
              event: ongr.pipeline.ongr_convert.provider.consume
              method: onConsume
            - name: kernel.event_listener
              event: ongr.pipeline.ongr_convert.provider.start
              method: onStart
            - name: kernel.event_listener
              event: ongr.pipeline.ongr_convert.provider.finish
              method: onFinish
..
