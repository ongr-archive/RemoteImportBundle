<?php

namespace ONGR\RemoteImportBundle\Utils;

use Symfony\Component\Console\Helper\ProgressHelper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Outputs progress bar to cli.
 */
class ProgressTracker
{
    /**
     * @var ProgressHelper
     */
    private $progress;

    /**
     * @param int             $count
     * @param OutputInterface $output
     */
    public function __construct($count, OutputInterface $output)
    {
        $this->progress = new ProgressHelper();
        $this->progress->start($output, $count);
        $this->output = $output;
    }

    /**
     * Move on to next.
     */
    public function done()
    {
        $this->progress->advance();
    }

    /**
     * Finish progress.
     */
    public function finish()
    {
        $this->progress->finish();
    }
}
