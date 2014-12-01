<?php

namespace ONGR\RemoteImportBundle\Tests\Functional;

/**
 * Function tests helper trait.
 */
trait TestHelperTrait
{
    /**
     * Check if one array is subset of another.
     *
     * @param array $needle
     * @param array $haystack
     */
    protected function assertArrayContainsArray($needle, $haystack)
    {
        foreach ($needle as $key => $val) {
            \PHPUnit_Framework_Assert::assertArrayHasKey($key, $haystack);

            if (is_array($val)) {
                $this->assertArrayContainsArray($val, $haystack[$key]);
            } else {
                \PHPUnit_Framework_Assert::assertEquals($val, $haystack[$key]);
            }
        }
    }
}
