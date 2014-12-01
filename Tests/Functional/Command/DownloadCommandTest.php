<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\RemoteImportBundle\Tests\Functional\Command;

use ONGR\RemoteImportBundle\Command\DownloadCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Functional test for ongr:remote:download command.
 */
class DownloadCommandTest extends WebTestCase
{
    /**
     * Tests command.
     */
    public function testCommand()
    {
        $kernel = self::createClient()->getKernel();

        $application = new Application($kernel);
        $application->add(new DownloadCommand());
        $command = $application->find('ongr:remote:download');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'provider' => 'provider_foo',
            ]
        );

        $dataFilename = $this->extractDataFilename($commandTester);
        $this->assertFileExists($dataFilename);
        $contents = file_get_contents($dataFilename);
        $this->assertEquals('content_foobar', $contents);

        $listener = $kernel->getContainer()->get('project.listener.dummy');
        $this->assertTrue($listener->isCalled(), 'listener must have been called');
    }

    /**
     * Extract downloaded file name from tester output.
     *
     * @param CommandTester $commandTester
     *
     * @return string
     */
    private function extractDataFilename($commandTester)
    {
        $displayText = $commandTester->getDisplay();
        $matches = [];
        preg_match('~\S+/data.txt~', $displayText, $matches);
        $dataFilename = $matches[0];
        $dataFilename = __DIR__ . '/../../app/data/' . $dataFilename;

        return $dataFilename;
    }
}
