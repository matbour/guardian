<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Exceptions;

use Windy\Guardian\Exceptions\InvalidClaimsConfigurationException;
use Windy\Guardian\Tests\GuardianTestCase;
use function preg_quote;

/**
 * @coversDefaultClass \Windy\Guardian\Exceptions\InvalidClaimsConfigurationException
 */
class InvalidClaimsConfigurationExceptionTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->expectException(InvalidClaimsConfigurationException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('config/guardian.php', '/') . '/');
        $this->expectExceptionMessageMatches('/claims.*foo-claims/s');
        throw new InvalidClaimsConfigurationException('foo-claims');
    }
}
