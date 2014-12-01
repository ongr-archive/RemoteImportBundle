<?php

namespace ONGR\RemoteImportBundle\Service\DataConverter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * This interface defines behavior for data converter.
 */
interface DataConverterInterface extends \Traversable, \Countable
{
    /**
     * Tells what is data type provided by converter.
     *
     * SyncTaskCompleteEvent::DATA_TYPE_FULL_DOCUMENTS or SyncTaskCompleteEvent::DATA_TYPE_PARTIAL_DOCUMENTS
     *
     * @return string
     */
    public function getDataType();

    /**
     * Returns name of target repository for converted objects (product, offer, content and so on...).
     *
     * @return string|null
     */
    public function getRepository();

    /**
     * Do all preparation tasks before data convert.
     *
     * @param OutputInterface|null $output
     */
    public function load(OutputInterface $output = null);
}
