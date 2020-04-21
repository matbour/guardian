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
     * @testWith ["foo/bar", null, "/this feature.*foo\\/bar/"]
     *           ["bar/baz", "lorem", "/lorem.*bar\\/baz/"]
     * @covers ::__construct
     *
     * @param string      $library The missing library.
     * @param string|null $reason  The reason.
     * @param string      $pattern The expected pattern.
     */
    public function testConstruct(string $library, ?string $reason, string $pattern): void
    {
        $this->expectException(MissingLibraryException::class);
        $this->expectExceptionMessageMatches($pattern);
        throw new MissingLibraryException($library, $reason);
    }
}
