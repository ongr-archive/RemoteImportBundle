<?php

namespace ONGR\RemoteImportBundle\Service\DataConverter;

/**
 * This interface defines behavior for data converter when source is taken from file system.
 */
interface FileAwareConverterInterface extends DataConverterInterface
{
    /**
     * Set file or directory name for source data.
     *
     * @param string $file
     */
    public function setFileName($file);

    /**
     * Set converter type.
     *
     * @param string $type
     */
    public function setType($type);
}
