<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Exceptions;

use Windy\Guardian\Exceptions\MissingLibraryException;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Exceptions\MissingLibraryException
 */
class MissingLibraryExceptionTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $instance = new MissingLibraryException('foo/bar');
        $this->assertMatchesRegularExpression('/this feature.*foo\/bar/', $instance->getMessage());

        $instance = new MissingLibraryException('bar/baz', 'lorem');
        $this->assertMatchesRegularExpression('/lorem.*bar\/baz/', $instance->getMessage());
    }
}
