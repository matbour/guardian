<?php

declare(strict_types=1);

namespace Windy\Guardian\Tests\E2E;

use App\Http\Controllers\AuthController;
use App\User;
use Carbon\Carbon;
use Hydra\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Windy\Guardian\Tests\GuardianTestCase;
use function json_decode;

/**
 * @coversNothing
 */
class LoginTest extends GuardianTestCase
{
    use DatabaseMigrations;

    public function testLogin(): void
    {
        DB::table('users')->insert([
            'name'       => 'mathieu',
            'email'      => 'mathieu@mathrix.fr',
            'password'   => $this->app->make('hash')->make('123456'),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Route::post('/login', AuthController::class . '@login');

        $this->app->make('config')->set('auth', [
            'defaults'  => ['guard' => 'guardian'],
            'guards'    => [
                'guardian' => [
                    'driver'    => 'guardian',
                    'provider'  => 'users',
                    'authority' => 'default',
                ],
            ],
            'providers' => [
                'users' => [
                    'driver' => 'eloquent',
                    'model'  => User::class,
                ],
            ],
        ]);

        $response = $this->call('POST', '/login', [
            'email'    => 'mathieu@mathrix.fr',
            'password' => '123456',
        ]);

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $data);
    }
}
