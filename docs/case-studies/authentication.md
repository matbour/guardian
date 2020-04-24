# Authenticate users with Guardian

## Objective

In this example, we will implement the user authentication in Laravel using Guardian.

```mermaid
sequenceDiagram
autonumber
User->>Controller: Sends credentials
activate Controller
Controller->>Controller: Check credentials
alt valid
Controller->>Guardian: Sends payload to sign
activate Guardian
Guardian-->>Controller: Generate serialized JWT
deactivate Guardian
Controller-->>User: Sends serialized JWT
else invalid
Controller-->>User: Sends credentials error
end
deactivate Controller
```

The key-actions are the following:

1. The user sends its credentials in a POST request.
2. The controller validates its credentials.
3. The controller generates the payload to sign.
4. Guardian sign the payload by generating a token for the user.
5. The token is sent back to the user.
6. If the credentials are invalid, the controller sends an error to the user.

## Implementation

### Configuration
In this example, we will use a `"login"` ECDSA key, configured with the `ES512` algorithm and the `P-521` curve.
This is a quite good compromise between security and speed.
See the [configuration documentation](/reference/configuration) for the list of all available cryptographic algorithms.

#### `config/guardian.php`
In the `config/guardian.php` file, add the following configuration:

```php
// config/guardian.php
return [
    'defaults' => [
        'authority' => 'login',
    ],
    'authorities' => [
        // your other authorities definitions
        'login' => [
            'key'    => 'login',
            'claims' => 'login'
        ]
    ],
    'keys' => [
        // your other keys definitions
        'login' => [
            'algorithm' => 'ES512',
            'curve'     => 'P-521',
            'path'      => storage_path('login.json'),
        ]
    ],
    'claims' => [
        // your others claims definitions
        'login' => [
            'iss' => 'My application',
            'aud' => 'My audience',
            'exp' => '+15 days',
            'iat' => 'now',
            'nbf' => 'now',
            'jid' => 'uuid',
        ]    
    ]
];
```

#### `config/auth.php`
```php
// config/auth.php
return [
    'guards' => [
        'guardian' => [
            'driver'    => 'guardian',
            'provider'  => 'eloquent',
            'authority' => 'login',
        ]
    ]    
];
```

### Controller action
In the controller of your choice, create a new action.
In this example, we will use a dummy `AuthController`, located in `app/Http/Controllers` directory.

<<< @/../sandbox/app/Http/Controllers/AuthController.php

Using the `Guardian` facade, we can sign a token for the given user.

### Register the route

To finish, we simply have to register our route with something like:

#### Laravel
```php
// routes/api.php
Route::post('/login', App\Http\Controllers\AuthController::class . '@login');
```

#### Lumen
```php
// routes/web.php
$router->post('/login', App\Http\Controllers\AuthController::class . '@login');
```

## Test the login chain

Send a post request to the route with the user credentials:

```bash
# For example, using curl
curl -X POST -d email=mathieu@mathrix.fr&password=123456
```

The expected JSON response is the following:

```json
{
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIxIiwiaXNzIjoiWW91ciBJc3N1ZXIiLCJhdWQiOiJZb3VyIEF1ZGllbmNlIiwiZXhwIjoxNTk1NTU5NTY1LCJuYmYiOjE1ODc2OTcxNjUsImlhdCI6MTU4NzY5NzE2NSwiamlkIjoiZmU0ZDhlNDctZjRiMi00NDE5LWFhOTUtODFlNDMxNzFlNzA0In0.9LZgKaOwZpj3OKbpLb-cuE0AurZb2lO7ekN7eAGKi4_aKE1LxvJhCVkwFsfKkjqTwVkPshyHjTLAeYl03qGrFQ"
}
```

You can debug this token on [jwt.io](https://jwt.io/#debugger-io?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIxIiwiaXNzIjoiWW91ciBJc3N1ZXIiLCJhdWQiOiJZb3VyIEF1ZGllbmNlIiwiZXhwIjoxNTk1NTU5NTY1LCJuYmYiOjE1ODc2OTcxNjUsImlhdCI6MTU4NzY5NzE2NSwiamlkIjoiZmU0ZDhlNDctZjRiMi00NDE5LWFhOTUtODFlNDMxNzFlNzA0In0.9LZgKaOwZpj3OKbpLb-cuE0AurZb2lO7ekN7eAGKi4_aKE1LxvJhCVkwFsfKkjqTwVkPshyHjTLAeYl03qGrFQ).
