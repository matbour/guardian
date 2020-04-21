<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Exceptions;

use Windy\Guardian\Exceptions\InvalidGuardConfigurationException;
use Windy\Guardian\Tests\GuardianTestCase;
use function preg_quote;

/**
 * @coversDefaultClass \Windy\Guardian\Exceptions\InvalidGuardConfigurationException
 */
class InvalidGuardConfigurationExceptionTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->expectException(InvalidGuardConfigurationException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('config/auth.php', '/') . '/');
        $this->expectExceptionMessageMatches('/guards.*foo-guard/s');
        throw new InvalidGuardConfigurationException('foo-guard');
    }
}
