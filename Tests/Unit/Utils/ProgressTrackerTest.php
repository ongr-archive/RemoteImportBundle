<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Utils;

use ONGR\RemoteImportBundle\Utils\ProgressTracker;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test for ProgressTracker.
 */
class ProgressTrackerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider.
     *
     * @return mixed
     */
    public function getTestDoneData()
    {
        $out = [];

        // Case #0.
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $output
            ->expects($this->exactly(19))
            ->method('writeln');

        $count = 19;

        $out[] = [
            $output,
            $count,
        ];

        return $out;
    }

    /**
     * Tests done() method and test if we do not have too much writes.
     *
     * @param OutputInterface $output
     * @param int             $count
     *
     * @dataProvider getTestDoneData
     */
    public function testDone($output, $count)
    {
        $tracker = new ProgressTracker($count, $output);

        for ($i = 0; $i < $count; $i++) {
            $tracker->done();
        }

        $tracker->finish();
    }
}
