<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Service\DataConverter;

use ONGR\RemoteImportBundle\Service\DataConverter\AbstractXMLConverter;
use ONGR\ConnectionsBundle\Service\ImportDataDirectory;

/**
 * Unit tests for AbstractXMLConverter.
 */
class AbstractXMLConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Check if expected exception is thrown when we try to use a non-existent file.
     *
     * @expectedException \DomainException
     * @expectedExceptionMessage Could not open file
     */
    public function testMissingFile()
    {
        $converter = $this->getConverter();
        $converter->setFileName('non-existent-file.xml');
        iterator_to_array($converter);
    }

    /**
     * Check if depth setter and getter works as expected.
     */
    public function testDepthSetter()
    {
        $converter = $this->getConverter();
        $this->assertEquals(1, $converter->getDepth());

        $converter->setDepth(2);
        $this->assertEquals(2, $converter->getDepth());
    }

    /**
     * Returns converted for testing.
     *
     * @return AbstractXMLConverter|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getConverter()
    {
        $dir = new ImportDataDirectory(__DIR__, '../../Fixtures/Import');
        /** @var AbstractXMLConverter|\PHPUnit_Framework_MockObject_MockObject $converter */
        $converter = $this->getMockBuilder('ONGR\RemoteImportBundle\Service\DataConverter\AbstractXMLConverter')
            ->setMethods(['convertItem', 'getObjectTag'])
            ->getMockForAbstractClass();
        $converter->setDir($dir);
        $converter->setProvider('');

        return $converter;
    }

    /**
     * Tests AbstractXMLConverter::count(),  AbstractXMLConverter::rewind(),  AbstractXMLConverter::key().
     */
    public function testIterator()
    {
        $converter = $this->getConverter();
        $converter->expects($this->any())->method('getObjectTag')->willReturn('product');
        $converter->setFileName('product.xml');
        $this->assertEquals(1, count($converter));
        $this->assertEquals(1, count($converter));

        $counter = 0;
        foreach ($converter as $key => $value) {
            $this->assertEquals($counter++, $key);
            $this->assertEmpty($value);
        }
        $counter = 0;
        foreach ($converter as $key => $value) {
            $this->assertEquals($counter++, $key);
            $this->assertEmpty($value);
        }
    }
}
