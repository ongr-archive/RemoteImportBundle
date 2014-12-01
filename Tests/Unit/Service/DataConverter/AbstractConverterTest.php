<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Service\DataConverter;

use ONGR\RemoteImportBundle\Service\DataConverter\AbstractConverter;
use ONGR\ConnectionsBundle\Service\ImportDataDirectory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Symfony\Bridge\Monolog\Logger;

/**
 * Test for AbstractConverter.
 */
class AbstractConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param ImportDataDirectory $directory
     * @param string              $provider
     *
     * @return AbstractConverter
     */
    protected function getConverter($directory, $provider)
    {
        /** @var AbstractConverter|MockObject $object */
        $object = $this->getMockForAbstractClass(
            'ONGR\RemoteImportBundle\Service\DataConverter\AbstractConverter'
        );
        $object->setDir($directory);
        $object->setProvider($provider);

        return $object;
    }

    /**
     * Tests file path generator.
     */
    public function testFilePath()
    {
        $dir = new ImportDataDirectory('/var/www/whatever/app', 'data');
        $converter = $this->getConverter($dir, 'nfq');
        $converter->setFileName('full.batch');

        $this->assertEquals('/var/www/whatever/app/data/full.batch', $converter->getFilePath());
    }

    /**
     * Test whether abstract converter's repository is null.
     */
    public function testGetRepository()
    {
        $dir = new ImportDataDirectory('/var/www/whatever/app', 'data');
        $converter = $this->getConverter($dir, 'nfq');

        $this->assertNull($converter->getRepository());
    }

    /**
     * Test exception throwing.
     *
     * @expectedException \ONGR\RemoteImportBundle\Utils\Exception\NotCountableException
     */
    public function testCount()
    {
        $converter = $this->getConverter(null, 'nfq');
        count($converter);
    }

    /**
     * Tests setting and getting logger.
     */
    public function testSetGetLogger()
    {
        $converter = $this->getConverter(null, 'ongr');

        $logger = new Logger('');

        $converter->setLogger($logger);

        $this->assertSame($logger, $converter->getLogger());
    }

    /**
     * Tests BadMethodCallException throwing.
     *
     * @expectedException \BadMethodCallException
     */
    public function testSetTypeIsNotCallable()
    {
        $converter = $this->getConverter(null, null);
        $converter->setType('ShouldNotWork');
    }
}
