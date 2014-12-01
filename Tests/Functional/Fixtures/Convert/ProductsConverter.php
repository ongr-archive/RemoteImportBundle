<?php

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 */

namespace ONGR\RemoteImportBundle\Tests\Functional\Fixtures\Convert;

use ONGR\RemoteImportBundle\Service\DataConverter\AbstractXMLConverter;
use ONGR\RemoteImportBundle\Tests\Model\ProductModel;

/**
 * Dummy class for SyncConvertFileCommand functional test.
 */
class ProductsConverter extends AbstractXMLConverter
{
    /**
     * @var string The tag which will be looked for in xml to distinguish object
     */
    protected $objectTag = 'product';

    /**
     * {@inheritdoc}
     */
    public function getObjectTag()
    {
        return $this->objectTag;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertItem(\SimpleXMLElement $item)
    {
        $model = new ProductModel;

        $model->id = (string)$item->attributes()[0];
        $model->title = (string)$item->title;
        $model->sku = (integer)$item->sku;
        $model->description = (string)$item->description;
        $model->price = (float)$item->price;
        $model->image = [$item->image];
        $model->manufacturer = (string)$item->manufacturer;
        $model->longDescription = (string)$item->longDescription;
        $model->url = [$item->url];
        $model->url_lowercased = [$item->url_lowercased];

        return $model;
    }
}
