<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Exceptions;

use Windy\Guardian\Exceptions\InvalidAlgorithmExceptionException;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Exceptions\InvalidAlgorithmExceptionException
 */
class InvalidAlgorithmExceptionTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->expectException(InvalidAlgorithmExceptionException::class);
        $this->expectExceptionMessageMatches('/algorithm.*foo-alg/');
        throw new InvalidAlgorithmExceptionException('foo-alg');
    }
}
