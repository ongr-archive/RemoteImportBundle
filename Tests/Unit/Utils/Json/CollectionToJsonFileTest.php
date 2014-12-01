<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Utils\Json;

use ONGR\RemoteImportBundle\Tests\Model\ProductModel;
use ONGR\RemoteImportBundle\Utils\Json\CollectionToJsonFile;
use ONGR\RemoteImportBundle\Utils\ProgressTracker;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Test for CollectionToJsonFile.
 */
class CollectionToJsonFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Data provider.
     *
     * @return mixed
     */
    public function getTestSerializeAndSaveData()
    {
        $data = [];

        // Case #0.
        $obj = [];
        $obj[] = ['count' => 1];
        $arr = [
            '_id' => 1,
            '_score' => null,
            '_type' => 'product',
            '_source' => [
                'id' => 1,
                'sku' => null,
                'title' => null,
                'description' => null,
                'price' => null,
                'score' => null,
                'parent' => null,
                'ttl' => null,
                'highlight' => null,
            ],
        ];
        $obj[] = $arr;

        $data[] = [
            [
                ['id' => 1],
            ],
            [],
            json_encode($obj),
        ];

        // Case #1.
        $obj = [];
        $obj[] = ['count' => 1, 'test' => 5];
        $arr = [
            '_id' => 1,
            '_score' => null,
            '_type' => 'product',
            '_source' => [
                'id' => 1,
                'sku' => null,
                'title' => null,
                'description' => null,
                'price' => null,
                'score' => null,
                'parent' => null,
                'ttl' => null,
                'highlight' => null,
            ],
        ];
        $obj[] = $arr;

        $data[] = [
            [
                ['id' => 1],
            ],
            ['test' => 5],
            json_encode($obj),
        ];

        // Case #3 Null element in the collection. Should report invalid count.
        $obj = [];
        $obj[] = ['count' => 2];
        $arr = [
            '_id' => null,
            '_score' => null,
            '_type' => 'product',
            '_source' => [
                'id' => null,
                'sku' => null,
                'title' => null,
                'description' => null,
                'price' => null,
                'score' => null,
                'parent' => null,
                'ttl' => null,
                'highlight' => null,
            ],
        ];
        $obj[] = $arr;
        $arr['_id'] = 1;
        $arr['_source']['id'] = 1;
        $obj[] = $arr;

        $data[] = [
            [
                null,
                ['id' => 1],
            ],
            [],
            json_encode($obj),
        ];

        return $data;
    }

    /**
     * Assigns data to ProductModel.
     *
     * @param ProductModel $object
     * @param mixed        $data
     *
     * @return ProductModel
     */
    public function assignObjectData(ProductModel $object, $data)
    {
        if (is_array($data)) {
            foreach ($data as $property => $value) {
                $object->__set($property, $value);
            }
        }

        return $object;
    }

    /**
     * Tests method serializeAndSave().
     *
     * @param mixed  $data
     * @param mixed  $metadata
     * @param string $output
     *
     * @dataProvider getTestSerializeAndSaveData
     */
    public function testSerializeAndSave($data, $metadata, $output)
    {
        /** @var ProgressTracker|MockObject $tracker */
        $tracker = $this
            ->getMockBuilder('ONGR\RemoteImportBundle\Utils\ProgressTracker')
            ->disableOriginalConstructor()
            ->getMock();

        $tracker
            ->expects($this->any())
            ->method('done');

        $tracker
            ->expects($this->once())
            ->method('finish');

        $helper = new CollectionToJsonFile();
        $helper->setTracker($tracker);

        $entries = [];
        foreach ($data as $entry) {
            $entries[] = $this->assignObjectData(new ProductModel(), $entry);
        }

        $helper->serializeAndSave('output.json', $entries, $metadata);

        $this->assertFileExists('output.json');
        $this->assertEquals(json_decode($output), json_decode(str_replace("\n", '', file_get_contents('output.json'))));
        unlink('output.json');
    }

    /**
     * Tests if an exception is thrown when incorrect directory is specified.
     *
     * @expectedException \ONGR\RemoteImportBundle\Utils\Exception\FileCreateException
     */
    public function testInvalidFile()
    {
        $helper = new CollectionToJsonFile();
        $helper->serializeAndSave('/bla/bla/bla/tmp.tmp.tmp', [], []);
    }
}
