<?php

namespace ONGR\RemoteImportBundle\Tests\Functional\Command;

use ONGR\RemoteImportBundle\Command\SyncConvertFileCommand;
use ONGR\RemoteImportBundle\Tests\Functional\TestHelperTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use ONGR\ElasticsearchBundle\Command\IndexImportCommand;

/**
 * Functional test for ongr:remote:convert-file command.
 */
class SyncConvertFileCommandTest extends WebTestCase
{
    use TestHelperTrait;

    /**
     * Generates path for a given file name.
     *
     * @param string $fileName
     *
     * @return string
     */
    protected function generateFilePath($fileName)
    {
        return __DIR__ . '/../Fixtures/Convert/' . $fileName;
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $fileName = $this->generateFilePath('product.xml.converted.json');

        if (file_exists($fileName)) {
            unlink($fileName);
        }

        parent::tearDown();
    }

    /**
     * Check if file passed to the converter is converted properly.
     */
    public function testExecute()
    {
        $kernel = self::createClient()->getKernel();

        $application = new Application($kernel);
        $application->add(new SyncConvertFileCommand());
        $application->add(new IndexImportCommand());

        $command = $application->find('ongr:remote:convert-file');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName(),
                'provider' => 'product',
                'file' => $this->generateFilePath('product.xml'),
            ]
        );

        $expected = json_decode(file_get_contents($this->generateFilePath('expected_product.json')), true);
        $actual = json_decode(file_get_contents($this->generateFilePath('product.xml.converted.json')), true);

        $this->assertArrayContainsArray($expected, $actual);

        $importCommand = $application->find('ongr:es:index:import');
        $importCommandTester = new CommandTester($importCommand);
        $importCommandTester->execute(
            [
                'command' => $importCommand->getName(),
                'filename' => $this->generateFilePath('product.xml.converted.json'),
                '--raw' => true,
            ]
        );

        $listener = $kernel->getContainer()->get('project.listener.dummy');
        $this->assertTrue($listener->isCalled(), 'listener must have been called');
    }
}
