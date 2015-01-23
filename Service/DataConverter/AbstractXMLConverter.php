<?php

namespace ONGR\RemoteImportBundle\Service\DataConverter;

use ONGR\ElasticsearchBundle\Document\DocumentInterface;
use Symfony\Component\DependencyInjection\SimpleXMLElement;

/**
 * Basic XML converter to be extended with your functionality.
 */
abstract class AbstractXMLConverter extends AbstractConverter
{
    /**
     * @var bool Is current key valid.
     */
    protected $valid = true;

    /**
     * @var int Current key.
     */
    protected $key;

    /**
     * @var \XMLReader
     */
    protected $xmlReader;

    /**
     * @var DocumentInterface Cache for current model.
     */
    protected $cache;

    /**
     * @var int Cache for number of objects in xml.
     */
    protected $count;

    /**
     * @var int Depth in which we will look for object tags.
     */
    protected $depth = 1;

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        return $this->cache;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        $this->key++;
        $this->cacheModel();
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        return $this->valid;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->key = 0;
        if ($this->xmlReader !== null) {
            $this->xmlReader->close();
        }
        $this->xmlReader = $this->getNewXMLReader();
        $this->cacheModel();
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (isset($this->count)) {
            return $this->count;
        }

        $reader = $this->getNewXMLReader();
        $this->count = 0;
        while ($this->advance($reader)) {
            $this->count++;
        }

        return $this->count;
    }

    /**
     * Sets depth in which we will look for object tags.
     *
     * @param int $depth
     */
    public function setDepth($depth)
    {
        $this->depth = $depth;
    }

    /**
     * Returns depth in which we will look for object tags.
     *
     * @return int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Returns updated model.
     *
     * @param \SimpleXMLElement $item
     *
     * @return DocumentInterface|null
     */
    abstract protected function convertItem(\SimpleXMLElement $item);

    /**
     * Tag which will be looked for in xml to distinguish object.
     *
     * @return string
     */
    abstract protected function getObjectTag();

    /**
     * Gets and returns updated model.
     */
    protected function cacheModel()
    {
        $xmlObject = $this->getSimpleXmlObject();
        if ($xmlObject === null) {
            return;
        }

        $this->cache = $this->convertItem($xmlObject);
    }

    /**
     * Creates a new xml reader.
     *
     * @return \XMLReader
     *
     * @throws \DomainException
     */
    protected function getNewXMLReader()
    {
        $path = $this->getFilePath();

        $xml = new \XMLReader();
        if (!@$xml->open($path)) {
            throw new \DomainException("Could not open file {$path} with XMLReader");
        }

        return $xml;
    }

    /**
     * Returns SimpleXmlObject node with model data from xml file.
     *
     * @throws \UnderflowException
     *
     * @return null|SimpleXMLElement
     */
    protected function getSimpleXmlObject()
    {
        if (!$this->advance($this->xmlReader)) {
            $this->valid = false;

            return null;
        }

        $this->valid = true;
        $out = $this->xmlReader->readOuterXml();

        return new \SimpleXMLElement($out);
    }

    /**
     * Searches for the next model item in xml file.
     *
     * @param \XMLReader $reader
     *
     * @return bool
     */
    protected function advance(\XMLReader $reader)
    {
        while ($reader->read()) {
            if ($this->checkIfValid($reader)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if current reader position is valid.
     *
     * @param \XMLReader $reader
     *
     * @return bool
     */
    protected function checkIfValid(\XMLReader $reader)
    {
        return ($reader->nodeType === \XMLReader::ELEMENT
            && $reader->depth == $this->depth
            && $reader->name == $this->getObjectTag());
    }
}
