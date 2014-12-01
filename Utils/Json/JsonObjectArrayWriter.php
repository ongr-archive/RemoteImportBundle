<?php

namespace ONGR\RemoteImportBundle\Utils\Json;

/**
 * Serializes entities one by one.
 */
class JsonObjectArrayWriter
{
    /**
     * @var resource
     */
    private $handle;

    /**
     * @var array
     */
    private $metadata;

    /**
     * @var int
     */
    private $startOffset;

    /**
     * @var int
     */
    private $curCount;

    /**
     * @var bool
     */
    private $open = true;

    /**
     * @param resource $stream
     * @param int|null $count
     * @param array    $metadata
     */
    public function __construct($stream, $count = null, $metadata = [])
    {
        $this->handle = $stream;
        $this->startOffset = ftell($this->handle);

        $required = [];
        if ($count !== null) {
            $required['count'] = $count;
        } else {
            $required['countable'] = false;
        }
        $this->metadata = array_merge(
            $required,
            $metadata
        );

        $this->initialize();

        if (isset($this->metadata['count']) && $this->metadata['count'] == 0) {
            $this->finalize();
        }
    }

    /**
     * Performs initialization.
     *
     * @return void
     */
    private function initialize()
    {
        fwrite($this->handle, '[');
        fwrite($this->handle, "\n");
        fwrite($this->handle, json_encode($this->metadata));
        $this->curCount = 0;
    }

    /**
     * Performs finalization.
     *
     * @return void
     */
    public function finalize()
    {
        if ($this->open) {
            fwrite($this->handle, "\n");
            fwrite($this->handle, ']');
            $this->open = false;
        }
    }

    /**
     * Close stream.
     */
    public function close()
    {
        $this->finalize();
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;
        }
    }

    /**
     * Inserts entry to stream.
     *
     * @param mixed $entry
     *
     * @throws \OverflowException
     */
    public function push($entry)
    {
        $this->curCount++;

        if (isset($this->metadata['count']) && $this->curCount > $this->metadata['count']) {
            throw new \OverflowException('There are too much objects. Please increase expected number of items.');
        }

        $data = [
            '_id' => $entry->getId(),
            '_score' => $entry->getScore(),
            '_type' => $entry->getType(),
            '_source' => (array)$entry,
        ];

        fwrite($this->handle, ',' . "\n");
        fwrite($this->handle, json_encode($data));

        if (isset($this->metadata['count']) && $this->curCount == $this->metadata['count']) {
            $this->finalize();
        }
    }
}
