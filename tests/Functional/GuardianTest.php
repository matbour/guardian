<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\Functional;

use Laravel\Lumen\Application as LumenApplication;
use Windy\Guardian\Guardian;
use Windy\Guardian\Tests\GuardianTestCase;

/**
 * @coversDefaultClass \Windy\Guardian\Guardian
 */
class GuardianTest extends GuardianTestCase
{
    /**
     * @covers ::getFacadeAccessor
     */
    public function testGetFacadeAccessor(): void
    {
        if ($this->app instanceof LumenApplication) {
            $this->app->withFacades();
        }

        $token = Guardian::sign(['foo' => 'bar']);
        $this->assertTrue(Guardian::verify($token));
        $this->assertTrue(Guardian::check($token));
    }
}
