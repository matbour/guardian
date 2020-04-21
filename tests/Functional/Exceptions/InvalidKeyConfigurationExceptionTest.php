<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional\Exceptions;

use Windy\Guardian\Exceptions\InvalidKeyConfigurationException;
use Windy\Guardian\Tests\GuardianTestCase;
use function preg_quote;

/**
 * @coversDefaultClass \Windy\Guardian\Exceptions\InvalidKeyConfigurationException
 */
class InvalidKeyConfigurationExceptionTest extends GuardianTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $this->expectException(InvalidKeyConfigurationException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('config/guardian.php', '/') . '/');
        $this->expectExceptionMessageMatches('/keys.*foo-key/s');
        throw new InvalidKeyConfigurationException('foo-key');
    }
}
