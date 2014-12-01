<?php

namespace ONGR\RemoteImportBundle\Tests\Unit\Utils\Json;

use ONGR\RemoteImportBundle\Tests\Model\ProductModel;
use ONGR\RemoteImportBundle\Utils\Json\JsonObjectArrayWriter;

/**
 * Test for JsonObjectArrayWriter.
 */
class JsonObjectArrayWriterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestConstructorData()
    {
        $data = [];

        // Case #0.
        $data[] = [
            0,
            [],
            "[\n{\"count\":0}\n]",
        ];

        // Case #1.
        $data[] = [
            0,
            ['version' => 1],
            "[\n{\"count\":0,\"version\":1}\n]",
        ];

        // Case #2.
        $data[] = [
            null,
            ['version' => 1],
            "[\n{\"countable\":false,\"version\":1}\n]",
        ];

        return $data;
    }

    /**
     * Tests constructor.
     *
     * @param int    $count
     * @param mixed  $metadata
     * @param string $expected
     *
     * @dataProvider getTestConstructorData
     *
     * @return void
     */
    public function testConstructor($count, $metadata, $expected)
    {
        $stream = $this->initEmptyStream();
        $writer = new JsonObjectArrayWriter($stream, $count, $metadata);
        $writer->finalize();

        $this->assertEquals($expected, $this->readStream($stream));
    }

    /**
     * Initializes stream.
     *
     * @return resource
     */
    private function initEmptyStream()
    {
        $stream = fopen('php://memory', 'w');

        return $stream;
    }

    /**
     * Reads from stream.
     *
     * @param resource $stream
     *
     * @return resource
     */
    private function readStream($stream)
    {
        fseek($stream, 0, SEEK_SET);

        return stream_get_contents($stream);
    }

    /**
     * Data provider for testCount().
     *
     * @return array
     */
    public function getTestCountData()
    {
        $data = [];
        $data[] = [
            [],
            0,
        ];

        return $data;
    }

    /**
     * Data provider for testPush().
     *
     * @return array
     */
    public function getTestPushData()
    {
        $data = [];

        // Case #0.
        $data[] = [
            [],
            '[{"count":0}]',
        ];

        // Case #1.
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
            [['id' => 1]],
            json_encode($obj),
        ];

        // Case #2.
        $obj = [];
        $obj[] = ['count' => 5];
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
        $arr['_id'] = 2;
        $arr['_source']['id'] = 2;
        $obj[] = $arr;
        $arr['_id'] = 3;
        $arr['_source']['id'] = 3;
        $obj[] = $arr;
        $arr['_id'] = 4;
        $arr['_source']['id'] = 4;
        $obj[] = $arr;
        $arr['_id'] = 5;
        $arr['_source']['id'] = 5;
        $obj[] = $arr;

        $data[] = [
            [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
                ['id' => 4],
                ['id' => 5],
            ],
            json_encode($obj),
        ];

        return $data;
    }

    /**
     * Assigns data to ProductModel.
     *
     * @param ProductModel $object
     * @param array        $data
     *
     * @return ProductModel
     */
    public function assignObjectData(ProductModel $object, array $data)
    {
        foreach ($data as $property => $value) {
            $object->__set($property, $value);
        }

        return $object;
    }

    /**
     * Tests push() method.
     *
     * @param mixed  $objects
     * @param string $expected
     *
     * @dataProvider getTestPushData
     */
    public function testPush($objects, $expected)
    {
        $stream = $this->initEmptyStream();
        $writer = new JsonObjectArrayWriter($stream, count($objects));

        foreach ($objects as $object) {
            $product = new ProductModel();
            $this->assignObjectData($product, $object);
            $writer->push($product);
        }

        $this->assertEquals(json_decode($expected), json_decode(str_replace("\n", '', $this->readStream($stream))));
    }

    /**
     * Data provider for testPushOverflow().
     *
     * @return array
     */
    public function getTestPushOverflowData()
    {
        $data = [];

        // Case #0.
        $data[] = [
            0,
            [
                ['id' => 1],
            ],
        ];

        // Case #1.
        $data[] = [
            1,
            [
                ['id' => 1],
                ['id' => 2],
            ],
        ];

        // Case #2.
        $data[] = [
            5,
            [
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
                ['id' => 4],
                ['id' => 5],
                ['id' => 6],
            ],
        ];

        return $data;
    }

    /**
     * Tests push() method's overflowing case.
     *
     * @param int   $count
     * @param mixed $objects
     *
     * @dataProvider getTestPushOverflowData
     *
     * @expectedException \OverflowException
     */
    public function testPushOverflow($count, $objects)
    {
        $stream = $this->initEmptyStream();
        $writer = new JsonObjectArrayWriter($stream, $count);

        foreach ($objects as $object) {
            $product = new ProductModel();
            $writer->Push($this->assignObjectData($product, $object));
        }
    }
}
