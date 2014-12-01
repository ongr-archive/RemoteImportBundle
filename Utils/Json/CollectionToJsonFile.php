<?php

namespace ONGR\RemoteImportBundle\Utils\Json;

use ONGR\RemoteImportBundle\Utils\Exception\FileCreateException;
use ONGR\RemoteImportBundle\Utils\Exception\NotCountableException;
use ONGR\RemoteImportBundle\Utils\ProgressTracker;

/**
 * Serializes collection and saves it to file.
 */
class CollectionToJsonFile
{
    /**
     * @var ProgressTracker
     */
    private $tracker;

    /**
     * @param ProgressTracker $tracker
     */
    public function setTracker(ProgressTracker $tracker)
    {
        $this->tracker = $tracker;
    }

    /**
     * Serializes and stores given collection to file.
     *
     * Temporary file is used to not create file before all data to it is processed
     *
     * @param string $fileName
     * @param mixed  $collection
     * @param mixed  $metadata
     *
     * @throws FileCreateException
     */
    public function serializeAndSave($fileName, $collection, $metadata = [])
    {
        $tmpFile = $fileName . '.tmp';

        /** @var Resource|bool $handle */
        $handle = @fopen($tmpFile, 'w');

        if (!($handle)) {
            throw new FileCreateException('Unable to create a file, check if directory exists');
        }

        $count = null;
        try {
            $count = count($collection);
        } catch (NotCountableException $e) {
            // Do nothing.
        }

        $writer = new JsonObjectArrayWriter($handle, $count, $metadata);
        foreach ($collection as $entry) {
            if (isset($entry) && $entry !== null) {
                $writer->push($entry);
            }
            $this->tracker && $this->tracker->done();
        }
        $writer->close();
        rename($tmpFile, $fileName);

        $this->tracker && $this->tracker->finish();
    }
}
