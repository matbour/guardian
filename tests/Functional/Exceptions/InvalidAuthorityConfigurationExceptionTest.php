<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Exceptions;

use Windy\Guardian\Exceptions\InvalidAuthorityConfigurationException;
use Windy\Guardian\Tests\GuardianTestCase;
use function preg_quote;

/**
 * @coversDefaultClass \Windy\Guardian\Exceptions\InvalidAuthorityConfigurationException
 */
class InvalidAuthorityConfigurationExceptionTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->expectException(InvalidAuthorityConfigurationException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('config/guardian.php', '/') . '/');
        $this->expectExceptionMessageMatches('/authorities.*foo-authority/s');
        throw new InvalidAuthorityConfigurationException('foo-authority');
    }
}
